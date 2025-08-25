<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the user's tasks.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = auth()->user();
        
        $filters = [];
        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }
        if ($request->filled('priority')) {
            $filters['priority'] = $request->priority;
        }
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        
        $tasks = $this->taskService->getUserTasks($user, $filters);
        
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): TaskResource
    {
        $user = auth()->user();
        $data = $this->taskService->validateTaskData($request->validated());
        
        $task = $this->taskService->createTask($user, $data);
        
        return new TaskResource($task);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): TaskResource
    {
        $user = auth()->user();
        
        // Check if user can access this task
        if (!$this->taskService->canAccessTask($user, $task)) {
            abort(403, 'Unauthorized to view this task.');
        }
        
        return new TaskResource($task->load('user'));
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $user = auth()->user();
        
        // Check if user can modify this task
        if (!$this->taskService->canModifyTask($user, $task)) {
            abort(403, 'Unauthorized to modify this task.');
        }
        
        $data = $this->taskService->validateTaskData($request->validated());
        $this->taskService->updateTask($task, $data);
        
        return new TaskResource($task->fresh());
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $user = auth()->user();
        
        // Check if user can delete this task
        if (!$this->taskService->canDeleteTask($user, $task)) {
            abort(403, 'Unauthorized to delete this task.');
        }
        
        $this->taskService->deleteTask($task);
        
        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }

    /**
     * Reorder tasks for the authenticated user.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*' => 'required|integer|exists:tasks,id'
        ]);

        $user = auth()->user();
        $taskOrders = $request->tasks;
        
        // Verify all tasks belong to the user
        $userTaskIds = $this->taskService->getUserTasks($user)->pluck('id')->toArray();
        $requestedTaskIds = array_values($taskOrders);
        
        if (array_diff($requestedTaskIds, $userTaskIds)) {
            abort(403, 'You can only reorder your own tasks.');
        }
        
        $success = $this->taskService->reorderTasks($user, $taskOrders);
        
        if ($success) {
            return response()->json([
                'message' => 'Tasks reordered successfully'
            ]);
        }
        
        return response()->json([
            'message' => 'Failed to reorder tasks'
        ], 500);
    }

    /**
     * Toggle the status of a task.
     */
    public function toggleStatus(Task $task): TaskResource
    {
        $user = auth()->user();
        
        // Check if user can modify this task
        if (!$this->taskService->canModifyTask($user, $task)) {
            abort(403, 'Unauthorized to modify this task.');
        }
        
        $this->taskService->toggleTaskStatus($task);
        
        return new TaskResource($task->fresh());
    }

    /**
     * Get task statistics for the authenticated user.
     */
    public function statistics(): JsonResponse
    {
        $user = auth()->user();
        $stats = $this->taskService->getUserTaskStatistics($user);
        
        return response()->json([
            'data' => $stats
        ]);
    }
}
