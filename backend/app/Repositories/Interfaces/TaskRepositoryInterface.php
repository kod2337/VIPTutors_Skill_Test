<?php

namespace App\Repositories\Interfaces;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get tasks for a specific user
     */
    public function getTasksForUser(int $userId, array $filters = []): Collection;

    /**
     * Get tasks by status
     */
    public function getTasksByStatus(int $userId, string $status): Collection;

    /**
     * Get tasks by priority
     */
    public function getTasksByPriority(int $userId, string $priority): Collection;

    /**
     * Search tasks by title or description
     */
    public function searchTasks(int $userId, string $search): Collection;

    /**
     * Get user task statistics
     */
    public function getUserTaskStatistics(int $userId): array;

    /**
     * Reorder tasks
     */
    public function reorderTasks(int $userId, array $taskOrders): bool;

    /**
     * Toggle task status
     */
    public function toggleTaskStatus(Task $task): bool;

    /**
     * Get next order value for user tasks
     */
    public function getNextOrderValue(int $userId): int;

    /**
     * Delete old tasks (older than specified days)
     */
    public function deleteOldTasks(int $days): int;
}
