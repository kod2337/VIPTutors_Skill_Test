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
        
        // Validate advanced search parameters
        $request->validate([
            'status' => 'nullable|in:pending,completed',
            'priority' => 'nullable|in:low,medium,high',
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => 'nullable|in:created_at,updated_at,title,priority,status,order',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:5|max:10',
            'page' => 'nullable|integer|min:1'
        ]);
        
        $filters = [];
        
        // Basic filters
        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }
        if ($request->filled('priority')) {
            $filters['priority'] = $request->priority;
        }
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        
        // Date filters
        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->date_from;
        }
        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->date_to;
        }
        
        // Sorting
        if ($request->filled('sort_by')) {
            $filters['sort_by'] = $request->sort_by;
            $filters['sort_direction'] = $request->get('sort_direction', 'asc');
        }
        
        // Pagination
        $perPage = $request->get('per_page', 10);
        $withPagination = $request->has('page') || $request->has('per_page');
        
        if ($withPagination) {
            $tasks = $this->taskService->getUserTasksPaginated($user, $filters, $perPage);
            return TaskResource::collection($tasks);
        } else {
            $tasks = $this->taskService->getUserTasks($user, $filters);
            return TaskResource::collection($tasks);
        }
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

    /**
     * Get search suggestions based on existing task titles
     */
    public function searchSuggestions(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1|max:100'
        ]);

        $user = auth()->user();
        $query = $request->get('query');
        
        $suggestions = $this->taskService->getSearchSuggestions($user, $query);
        
        return response()->json([
            'data' => $suggestions
        ]);
    }

    /**
     * Get task filter options (priorities, statuses, etc.)
     */
    public function filterOptions(): JsonResponse
    {
        return response()->json([
            'data' => [
                'statuses' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'completed', 'label' => 'Completed']
                ],
                'priorities' => [
                    ['value' => 'low', 'label' => 'Low Priority'],
                    ['value' => 'medium', 'label' => 'Medium Priority'],
                    ['value' => 'high', 'label' => 'High Priority']
                ],
                'sort_options' => [
                    ['value' => 'created_at', 'label' => 'Date Created'],
                    ['value' => 'updated_at', 'label' => 'Last Updated'],
                    ['value' => 'title', 'label' => 'Title'],
                    ['value' => 'priority', 'label' => 'Priority'],
                    ['value' => 'status', 'label' => 'Status'],
                    ['value' => 'order', 'label' => 'Custom Order']
                ]
            ]
        ]);
    }
}
