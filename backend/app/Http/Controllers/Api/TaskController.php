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
     * @OA\Get(
     *     path="/tasks",
     *     tags={"Tasks"},
     *     summary="Get user's tasks",
     *     description="Retrieve a paginated list of tasks for the authenticated user with optional filtering and sorting",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by task status",
     *         @OA\Schema(type="string", enum={"pending", "completed"})
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority (comma-separated for multiple)",
     *         @OA\Schema(type="string", example="high,medium")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search tasks by title or description",
     *         @OA\Schema(type="string", example="project")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter tasks created from this date",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter tasks created until this date",
     *         @OA\Schema(type="string", format="date", example="2024-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort by field",
     *         @OA\Schema(type="string", enum={"created_at", "updated_at", "title", "priority", "status", "order"})
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (5-10)",
     *         @OA\Schema(type="integer", minimum=5, maximum=10, example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tasks retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task")),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Validate advanced search parameters
        $request->validate([
            'status' => 'nullable|in:pending,completed',
            'priority' => 'nullable|string', // Allow comma-separated priorities
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => 'nullable|in:created_at,updated_at,title,priority,status,order',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:5|max:10',
            'page' => 'nullable|integer|min:1'
        ]);
        
        // Additional validation for priority values
        if ($request->filled('priority')) {
            $priorities = explode(',', $request->priority);
            $validPriorities = ['low', 'medium', 'high'];
            foreach ($priorities as $priority) {
                if (!in_array(trim($priority), $validPriorities)) {
                    return response()->json([
                        'message' => 'Invalid priority value.',
                        'errors' => ['priority' => ['Priority must be one of: low, medium, high']]
                    ], 422);
                }
            }
        }
        
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
     * @OA\Post(
     *     path="/tasks",
     *     tags={"Tasks"},
     *     summary="Create a new task",
     *     description="Create a new task for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description"},
     *             @OA\Property(property="title", type="string", example="Complete project documentation"),
     *             @OA\Property(property="description", type="string", example="Write comprehensive documentation for the project"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="medium"),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed"}, example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function store(StoreTaskRequest $request): TaskResource
    {
        $user = auth()->user();
        $data = $this->taskService->validateTaskData($request->validated());
        
        $task = $this->taskService->createTask($user, $data);
        
        return new TaskResource($task);
    }

    /**
     * @OA\Get(
     *     path="/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Get a specific task",
     *     description="Retrieve a specific task by ID (user can only access their own tasks)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Task ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not your task"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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
     * @OA\Put(
     *     path="/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Update a task",
     *     description="Update a specific task (user can only update their own tasks)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Task ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated task title"),
     *             @OA\Property(property="description", type="string", example="Updated task description"),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}, example="high"),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed"}, example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not your task"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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
     * @OA\Delete(
     *     path="/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Delete a task",
     *     description="Delete a specific task (user can only delete their own tasks)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Task ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Not your task"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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
     * @OA\Post(
     *     path="/tasks/reorder",
     *     tags={"Tasks"},
     *     summary="Reorder tasks",
     *     description="Reorder tasks for the authenticated user by providing an array of task IDs in the desired order",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tasks"},
     *             @OA\Property(
     *                 property="tasks",
     *                 type="array",
     *                 description="Array of task IDs in the desired order",
     *                 @OA\Items(type="integer"),
     *                 example={3, 1, 2}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tasks reordered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tasks reordered successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or unauthorized task access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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
