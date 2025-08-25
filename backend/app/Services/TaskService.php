<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    protected TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Get all tasks for a user with caching and filtering
     */
    public function getUserTasks(User $user, array $filters = []): Collection
    {
        $cacheKey = $this->generateCacheKey($user->id, $filters);
        
        return Cache::remember($cacheKey, 300, function () use ($user, $filters) {
            return $this->taskRepository->getTasksForUser($user->id, $filters);
        });
    }

    /**
     * Create a new task
     */
    public function createTask(User $user, array $data): Task
    {
        $data['user_id'] = $user->id;
        
        // Clear user's task cache
        $this->clearUserTaskCache($user->id);
        
        return $this->taskRepository->create($data);
    }

    /**
     * Update an existing task
     */
    public function updateTask(Task $task, array $data): bool
    {
        $result = $this->taskRepository->update($task->id, $data);
        
        if ($result) {
            // Clear user's task cache
            $this->clearUserTaskCache($task->user_id);
        }
        
        return $result;
    }

    /**
     * Delete a task
     */
    public function deleteTask(Task $task): bool
    {
        $userId = $task->user_id;
        $result = $this->taskRepository->delete($task->id);
        
        if ($result) {
            // Clear user's task cache
            $this->clearUserTaskCache($userId);
        }
        
        return $result;
    }

    /**
     * Toggle task status
     */
    public function toggleTaskStatus(Task $task): bool
    {
        $result = $this->taskRepository->toggleTaskStatus($task);
        
        if ($result) {
            // Clear user's task cache
            $this->clearUserTaskCache($task->user_id);
        }
        
        return $result;
    }

    /**
     * Reorder tasks for a user
     */
    public function reorderTasks(User $user, array $taskOrders): bool
    {
        $result = $this->taskRepository->reorderTasks($user->id, $taskOrders);
        
        if ($result) {
            // Clear user's task cache
            $this->clearUserTaskCache($user->id);
        }
        
        return $result;
    }

    /**
     * Get task statistics for a user
     */
    public function getUserTaskStatistics(User $user): array
    {
        $cacheKey = "task_stats_user_{$user->id}";
        
        return Cache::remember($cacheKey, 600, function () use ($user) {
            return $this->taskRepository->getUserTaskStatistics($user->id);
        });
    }

    /**
     * Get tasks by status
     */
    public function getTasksByStatus(User $user, string $status): Collection
    {
        return $this->taskRepository->getTasksByStatus($user->id, $status);
    }

    /**
     * Get tasks by priority
     */
    public function getTasksByPriority(User $user, string $priority): Collection
    {
        return $this->taskRepository->getTasksByPriority($user->id, $priority);
    }

    /**
     * Search tasks
     */
    public function searchTasks(User $user, string $search): Collection
    {
        return $this->taskRepository->searchTasks($user->id, $search);
    }

    /**
     * Check if user can access task
     */
    public function canAccessTask(User $user, Task $task): bool
    {
        return $task->user_id === $user->id || $user->is_admin;
    }

    /**
     * Check if user can modify task
     */
    public function canModifyTask(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

    /**
     * Check if user can delete task
     */
    public function canDeleteTask(User $user, Task $task): bool
    {
        // Only admins or task owners can delete tasks
        return $user->is_admin || $task->user_id === $user->id;
    }

    /**
     * Clean up old tasks
     */
    public function cleanupOldTasks(int $days = 30): int
    {
        $deletedCount = $this->taskRepository->deleteOldTasks($days);
        
        // Clear all task caches since we deleted tasks
        Cache::flush(); // In production, you might want to be more selective
        
        return $deletedCount;
    }

    /**
     * Generate cache key for user tasks
     */
    protected function generateCacheKey(int $userId, array $filters): string
    {
        $filterString = http_build_query($filters);
        return "tasks_user_{$userId}_" . md5($filterString);
    }

    /**
     * Clear all cached data for a user's tasks
     */
    protected function clearUserTaskCache(int $userId): void
    {
        // Clear task list cache patterns
        $patterns = [
            "tasks_user_{$userId}_*",
            "task_stats_user_{$userId}"
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
        
        // In a production environment with Redis, you might use:
        // Cache::tags(["user_tasks_{$userId}"])->flush();
    }

    /**
     * Validate task data
     */
    public function validateTaskData(array $data): array
    {
        // Additional business logic validation beyond Form Requests
        $validated = $data;
        
        // Ensure status is valid
        if (isset($validated['status']) && !in_array($validated['status'], ['pending', 'completed'])) {
            $validated['status'] = 'pending';
        }
        
        // Ensure priority is valid
        if (isset($validated['priority']) && !in_array($validated['priority'], ['low', 'medium', 'high'])) {
            $validated['priority'] = 'medium';
        }
        
        return $validated;
    }
}
