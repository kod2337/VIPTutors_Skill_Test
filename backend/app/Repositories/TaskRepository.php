<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
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
        $query = $this->model->newQuery()->forUser($userId);

        // Apply status filter
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Apply priority filter
        if (isset($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        // Apply search filter
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->ordered()
                    ->get();
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
