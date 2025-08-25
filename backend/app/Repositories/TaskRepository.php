<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /**
     * Get tasks for a specific user with optional filters
     */
    public function getTasksForUser(int $userId, array $filters = []): Collection
    {
        $query = $this->buildTaskQuery($userId, $filters);
        return $query->get();
    }

    /**
     * Get paginated tasks for a specific user with optional filters
     */
    public function getTasksForUserPaginated(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildTaskQuery($userId, $filters);
        return $query->paginate($perPage);
    }

    /**
     * Build task query with filters
     */
    protected function buildTaskQuery(int $userId, array $filters = [])
    {
        $query = $this->model->newQuery()->forUser($userId);

        // Apply status filter
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Apply priority filter
        if (isset($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        // Apply search filter (enhanced full-text search)
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply sorting
        if (isset($filters['sort_by'])) {
            $sortBy = $filters['sort_by'];
            $sortDirection = $filters['sort_direction'] ?? 'asc';
            
            // Validate sort fields
            $allowedSortFields = ['created_at', 'updated_at', 'title', 'priority', 'status', 'order'];
            if (in_array($sortBy, $allowedSortFields)) {
                if ($sortBy === 'priority') {
                    // Custom priority sorting
                    $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low') " . ($sortDirection === 'desc' ? 'DESC' : 'ASC'));
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }
            }
        } else {
            // Default ordering by order field
            $query->ordered();
        }

        return $query;
    }

    /**
     * Get tasks by status for a specific user
     */
    public function getTasksByStatus(int $userId, string $status): Collection
    {
        return $this->model->forUser($userId)
                          ->byStatus($status)
                          ->ordered()
                          ->get();
    }

    /**
     * Get tasks by priority for a specific user
     */
    public function getTasksByPriority(int $userId, string $priority): Collection
    {
        return $this->model->forUser($userId)
                          ->byPriority($priority)
                          ->ordered()
                          ->get();
    }

    /**
     * Search tasks by title or description for a specific user
     */
    public function searchTasks(int $userId, string $search): Collection
    {
        return $this->model->forUser($userId)
                          ->search($search)
                          ->ordered()
                          ->get();
    }

    /**
     * Get search suggestions based on existing task titles
     */
    public function getSearchSuggestions(int $userId, string $query): array
    {
        $suggestions = $this->model->forUser($userId)
            ->where('title', 'LIKE', "%{$query}%")
            ->selectRaw('DISTINCT title')
            ->orderBy('title')
            ->limit(10)
            ->pluck('title')
            ->toArray();

        // Also get common words from descriptions
        $descriptionWords = $this->model->forUser($userId)
            ->where('description', 'LIKE', "%{$query}%")
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->pluck('description')
            ->flatMap(function ($description) use ($query) {
                $words = str_word_count(strtolower($description), 1);
                return array_filter($words, function ($word) use ($query) {
                    return strlen($word) > 2 && stripos($word, strtolower($query)) !== false;
                });
            })
            ->unique()
            ->sort()
            ->take(5)
            ->values()
            ->toArray();

        return array_unique(array_merge($suggestions, $descriptionWords));
    }

    /**
     * Get task statistics for a specific user
     */
    public function getUserTaskStatistics(int $userId): array
    {
        $stats = $this->model->forUser($userId)
                            ->selectRaw('
                                COUNT(*) as total,
                                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                                SUM(CASE WHEN priority = "high" THEN 1 ELSE 0 END) as `high_priority`,
                                SUM(CASE WHEN priority = "medium" THEN 1 ELSE 0 END) as `medium_priority`,
                                SUM(CASE WHEN priority = "low" THEN 1 ELSE 0 END) as `low_priority`
                            ')
                            ->first();

        return [
            'total' => $stats->total ?? 0,
            'completed' => $stats->completed ?? 0,
            'pending' => $stats->pending ?? 0,
            'high_priority' => $stats->high_priority ?? 0,
            'medium_priority' => $stats->medium_priority ?? 0,
            'low_priority' => $stats->low_priority ?? 0,
            'completion_rate' => $stats->total > 0 ? round(($stats->completed / $stats->total) * 100, 2) : 0
        ];
    }

    /**
     * Reorder tasks for a specific user
     */
    public function reorderTasks(int $userId, array $taskOrders): bool
    {
        try {
            DB::beginTransaction();

            foreach ($taskOrders as $order => $taskId) {
                $this->model->where('id', $taskId)
                           ->where('user_id', $userId)
                           ->update(['order' => $order + 1]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Toggle task status between pending and completed
     */
    public function toggleTaskStatus(Task $task): bool
    {
        $newStatus = $task->status === 'pending' ? 'completed' : 'pending';
        return $task->update(['status' => $newStatus]);
    }

    /**
     * Get the next order value for user tasks
     */
    public function getNextOrderValue(int $userId): int
    {
        $maxOrder = $this->model->forUser($userId)->max('order');
        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Delete tasks older than specified days
     */
    public function deleteOldTasks(int $days): int
    {
        return $this->model->where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Override create to automatically set order
     */
    public function create(array $data): Task
    {
        if (!isset($data['order']) && isset($data['user_id'])) {
            $data['order'] = $this->getNextOrderValue($data['user_id']);
        }

        return parent::create($data);
    }
}
