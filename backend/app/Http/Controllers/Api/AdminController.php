<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/dashboard-stats",
     *     tags={"Admin"},
     *     summary="Get admin dashboard statistics",
     *     description="Retrieve comprehensive statistics for the admin dashboard",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_users", type="integer", example=150),
     *             @OA\Property(property="admin_users", type="integer", example=5),
     *             @OA\Property(property="regular_users", type="integer", example=145),
     *             @OA\Property(property="total_tasks", type="integer", example=1250),
     *             @OA\Property(property="completed_tasks", type="integer", example=800),
     *             @OA\Property(property="pending_tasks", type="integer", example=450),
     *             @OA\Property(property="high_priority_tasks", type="integer", example=75),
     *             @OA\Property(property="tasks_created_today", type="integer", example=25),
     *             @OA\Property(property="tasks_created_this_week", type="integer", example=150)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getDashboardStats(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'regular_users' => User::where('is_admin', false)->count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'pending_tasks' => Task::where('status', 'pending')->count(),
            'high_priority_tasks' => Task::where('priority', 'high')->count(),
            'tasks_created_today' => Task::whereDate('created_at', Carbon::today())->count(),
            'tasks_created_this_week' => Task::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'tasks_created_this_month' => Task::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
        ];

        return response()->json([
            'message' => 'Dashboard statistics retrieved successfully',
            'data' => $stats
        ]);
    }

    /**
     * Get all users with pagination and their task statistics
     */
    public function getUsers(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:5|max:50',
            'page' => 'nullable|integer|min:1',
            'search' => 'nullable|string|max:255',
            'role' => 'nullable|in:all,admin,user'
        ]);

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $role = $request->get('role', 'all');

        $query = User::with(['tasks' => function ($query) {
            $query->select('id', 'user_id', 'status', 'priority', 'created_at');
        }])
        ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
            $query->where('status', 'completed');
        }, 'tasks as pending_tasks_count' => function ($query) {
            $query->where('status', 'pending');
        }, 'tasks as high_priority_tasks_count' => function ($query) {
            $query->where('priority', 'high');
        }]);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($role !== 'all') {
            $isAdmin = $role === 'admin';
            $query->where('is_admin', $isAdmin);
        }

        $users = $query->paginate($perPage);

        // Add computed statistics for each user
        $users->getCollection()->transform(function ($user) {
            $user->completion_rate = $user->tasks_count > 0 
                ? round(($user->completed_tasks_count / $user->tasks_count) * 100, 1)
                : 0;
            
            $user->recent_activity = $user->tasks()
                ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
                ->count();

            return $user;
        });

        return response()->json([
            'message' => 'Users retrieved successfully',
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ]
        ]);
    }

    /**
     * Get detailed user information with all their tasks
     */
    public function getUserDetails(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:5|max:50',
            'page' => 'nullable|integer|min:1',
            'status' => 'nullable|in:all,pending,completed',
            'priority' => 'nullable|in:all,low,medium,high'
        ]);

        $perPage = $request->get('per_page', 15);
        $status = $request->get('status', 'all');
        $priority = $request->get('priority', 'all');

        // Get user tasks with filters
        $tasksQuery = $user->tasks();

        if ($status !== 'all') {
            $tasksQuery->where('status', $status);
        }

        if ($priority !== 'all') {
            $tasksQuery->where('priority', $priority);
        }

        $tasks = $tasksQuery->orderBy('created_at', 'desc')->paginate($perPage);

        // Get user statistics
        $userStats = [
            'total_tasks' => $user->tasks()->count(),
            'completed_tasks' => $user->tasks()->where('status', 'completed')->count(),
            'pending_tasks' => $user->tasks()->where('status', 'pending')->count(),
            'high_priority_tasks' => $user->tasks()->where('priority', 'high')->count(),
            'medium_priority_tasks' => $user->tasks()->where('priority', 'medium')->count(),
            'low_priority_tasks' => $user->tasks()->where('priority', 'low')->count(),
            'tasks_this_week' => $user->tasks()->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'completion_rate' => $user->tasks()->count() > 0 
                ? round(($user->tasks()->where('status', 'completed')->count() / $user->tasks()->count()) * 100, 1)
                : 0,
        ];

        return response()->json([
            'message' => 'User details retrieved successfully',
            'data' => [
                'user' => $user,
                'statistics' => $userStats,
                'tasks' => $tasks->items(),
                'meta' => [
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                    'from' => $tasks->firstItem(),
                    'to' => $tasks->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Get system-wide task statistics grouped by various criteria
     */
    public function getTaskStatistics(): JsonResponse
    {
        $stats = [
            'overview' => [
                'total_tasks' => Task::count(),
                'completed_tasks' => Task::where('status', 'completed')->count(),
                'pending_tasks' => Task::where('status', 'pending')->count(),
            ],
            'by_priority' => [
                'high' => Task::where('priority', 'high')->count(),
                'medium' => Task::where('priority', 'medium')->count(),
                'low' => Task::where('priority', 'low')->count(),
            ],
            'by_status_and_priority' => Task::select('status', 'priority', DB::raw('count(*) as count'))
                ->groupBy('status', 'priority')
                ->get()
                ->groupBy('status'),
            'recent_activity' => [
                'today' => Task::whereDate('created_at', Carbon::today())->count(),
                'yesterday' => Task::whereDate('created_at', Carbon::yesterday())->count(),
                'this_week' => Task::whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->count(),
                'last_week' => Task::whereBetween('created_at', [
                    Carbon::now()->subWeek()->startOfWeek(),
                    Carbon::now()->subWeek()->endOfWeek()
                ])->count(),
                'this_month' => Task::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(),
            ],
            'completion_trends' => Task::selectRaw('DATE(created_at) as date, 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_tasks')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];

        return response()->json([
            'message' => 'Task statistics retrieved successfully',
            'data' => $stats
        ]);
    }

    /**
     * Delete any task (admin privilege)
     */
    public function deleteTask(Task $task): JsonResponse
    {
        $taskTitle = $task->title;
        $taskOwner = $task->user->name;
        
        $task->delete();

        return response()->json([
            'message' => "Task '{$taskTitle}' owned by {$taskOwner} has been deleted successfully"
        ]);
    }

    /**
     * Update user admin status
     */
    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'is_admin' => 'required|boolean'
        ]);

        $user->update([
            'is_admin' => $request->is_admin
        ]);

        $role = $request->is_admin ? 'admin' : 'user';

        return response()->json([
            'message' => "User {$user->name} has been updated to {$role} role successfully",
            'data' => $user->fresh()
        ]);
    }

    /**
     * Get users with most tasks (top performers)
     */
    public function getTopPerformers(): JsonResponse
    {
        $topPerformers = User::withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
            $query->where('status', 'completed');
        }])
        ->having('tasks_count', '>', 0)
        ->orderBy('completed_tasks_count', 'desc')
        ->take(10)
        ->get()
        ->map(function ($user) {
            $user->completion_rate = $user->tasks_count > 0 
                ? round(($user->completed_tasks_count / $user->tasks_count) * 100, 1)
                : 0;
            return $user;
        });

        return response()->json([
            'message' => 'Top performers retrieved successfully',
            'data' => $topPerformers
        ]);
    }
}
