<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $taskService;
    private TaskRepository $taskRepository;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->taskRepository = app(TaskRepository::class);
        $this->taskService = new TaskService($this->taskRepository);
    }

    /**
     * Test task creation through service.
     */
    public function test_service_can_create_task(): void
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => 'high',
            'status' => 'pending',
        ];

        $task = $this->taskService->createTask($this->user, $taskData);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($taskData['title'], $task->title);
        $this->assertEquals($this->user->id, $task->user_id);
        $this->assertEquals(1, $task->order); // First task should have order 1
    }

    /**
     * Test task creation with automatic order assignment.
     */
    public function test_service_assigns_correct_order_to_new_tasks(): void
    {
        // Create first task
        $task1 = $this->taskService->createTask($this->user, [
            'title' => 'First Task',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        // Create second task
        $task2 = $this->taskService->createTask($this->user, [
            'title' => 'Second Task',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $this->assertEquals(1, $task1->order);
        $this->assertEquals(2, $task2->order);
    }

    /**
     * Test getting user tasks through service.
     */
    public function test_service_can_get_user_tasks(): void
    {
        // Create tasks for this user
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        
        // Create tasks for another user
        $otherUser = User::factory()->create();
        Task::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $tasks = $this->taskService->getUserTasks($this->user);

        $this->assertCount(3, $tasks);
        foreach ($tasks as $task) {
            $this->assertEquals($this->user->id, $task->user_id);
        }
    }

    /**
     * Test task filtering through service.
     */
    public function test_service_can_filter_tasks(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending', 'priority' => 'high']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'completed', 'priority' => 'high']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending', 'priority' => 'low']);

        // Filter by status
        $pendingTasks = $this->taskService->getUserTasks($this->user, ['status' => 'pending']);
        $this->assertCount(2, $pendingTasks);

        // Filter by priority
        $highPriorityTasks = $this->taskService->getUserTasks($this->user, ['priority' => 'high']);
        $this->assertCount(2, $highPriorityTasks);

        // Filter by both
        $pendingHighTasks = $this->taskService->getUserTasks($this->user, [
            'status' => 'pending',
            'priority' => 'high'
        ]);
        $this->assertCount(1, $pendingHighTasks);
    }

    /**
     * Test task updating through service.
     */
    public function test_service_can_update_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        $updateData = [
            'title' => 'Updated Title',
            'status' => 'completed',
            'priority' => 'low',
        ];

        $result = $this->taskService->updateTask($task, $updateData);

        $this->assertTrue($result);
        
        // Refresh the task from database to verify changes
        $task->refresh();
        $this->assertEquals($updateData['title'], $task->title);
        $this->assertEquals($updateData['status'], $task->status);
        $this->assertEquals($updateData['priority'], $task->priority);
    }

    /**
     * Test task deletion through service.
     */
    public function test_service_can_delete_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $taskId = $task->id;

        $result = $this->taskService->deleteTask($task);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    /**
     * Test task reordering through service.
     */
    public function test_service_can_reorder_tasks(): void
    {
        $task1 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 1]);
        $task2 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 2]);
        $task3 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 3]);

        $reorderData = [
            ['id' => $task3->id, 'order' => 1],
            ['id' => $task1->id, 'order' => 2],
            ['id' => $task2->id, 'order' => 3],
        ];

        $result = $this->taskService->reorderTasks($this->user, $reorderData);

        $this->assertTrue($result);
        
        // Refresh models and check new order
        $task1->refresh();
        $task2->refresh();
        $task3->refresh();

        $this->assertEquals(2, $task1->order);
        $this->assertEquals(3, $task2->order);
        $this->assertEquals(1, $task3->order);
    }

    /**
     * Test service permission methods.
     */
    public function test_service_permission_methods(): void
    {
        $userTask = Task::factory()->create(['user_id' => $this->user->id]);
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);
        $admin = User::factory()->create(['is_admin' => true]);

        // Test canAccessTask
        $this->assertTrue($this->taskService->canAccessTask($this->user, $userTask));
        $this->assertFalse($this->taskService->canAccessTask($this->user, $otherTask));
        $this->assertTrue($this->taskService->canAccessTask($admin, $otherTask)); // Admin can access any task

        // Test canModifyTask
        $this->assertTrue($this->taskService->canModifyTask($this->user, $userTask));
        $this->assertFalse($this->taskService->canModifyTask($this->user, $otherTask));
        $this->assertFalse($this->taskService->canModifyTask($admin, $otherTask)); // Admin cannot modify other user's tasks

        // Test canDeleteTask
        $this->assertTrue($this->taskService->canDeleteTask($this->user, $userTask));
        $this->assertFalse($this->taskService->canDeleteTask($this->user, $otherTask));
        $this->assertTrue($this->taskService->canDeleteTask($admin, $otherTask)); // Admin can delete any task
    }

    /**
     * Test task validation.
     */
    public function test_service_validates_task_data(): void
    {
        $invalidData = [
            'title' => 'Test Task',
            'status' => 'invalid_status',
            'priority' => 'invalid_priority',
        ];

        $validatedData = $this->taskService->validateTaskData($invalidData);

        $this->assertEquals('pending', $validatedData['status']);
        $this->assertEquals('medium', $validatedData['priority']);
        $this->assertEquals('Test Task', $validatedData['title']);
    }

    /**
     * Test getting user task statistics.
     */
    public function test_service_can_get_user_statistics(): void
    {
        // Create test tasks
        Task::factory()->count(3)->create(['user_id' => $this->user->id, 'status' => 'completed']);
        Task::factory()->count(2)->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending', 'priority' => 'high']);

        $stats = $this->taskService->getUserTaskStatistics($this->user);

        $this->assertArrayHasKey('total_tasks', $stats);
        $this->assertArrayHasKey('completed_tasks', $stats);
        $this->assertArrayHasKey('pending_tasks', $stats);
        $this->assertArrayHasKey('completion_rate', $stats);
        
        $this->assertEquals(6, $stats['total_tasks']);
        $this->assertEquals(3, $stats['completed_tasks']);
        $this->assertEquals(3, $stats['pending_tasks']);
    }

    /**
     * Test service validates task ownership for reordering.
     */
    public function test_service_validates_task_ownership_for_reordering(): void
    {
        $otherUser = User::factory()->create();
        $userTask = Task::factory()->create(['user_id' => $this->user->id, 'order' => 1]);
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id, 'order' => 1]);

        $reorderData = [
            ['id' => $userTask->id, 'order' => 1],
            ['id' => $otherTask->id, 'order' => 2], // Not owned by user
        ];

        $this->expectException(\InvalidArgumentException::class);

        $this->taskService->reorderTasks($this->user, $reorderData);
    }
}
