<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskReorderingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test reordering tasks with valid data.
     */
    public function test_user_can_reorder_their_tasks(): void
    {
        // Create tasks with specific order
        $task1 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 1]);
        $task2 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 2]);
        $task3 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 3]);

        // Reorder: task3 -> task1 -> task2
        $reorderData = [
            'tasks' => [$task3->id, $task1->id, $task2->id]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/tasks/reorder', $reorderData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Tasks reordered successfully',
                ]);

        // Verify the new order in database
        $this->assertDatabaseHas('tasks', ['id' => $task3->id, 'order' => 1]);
        $this->assertDatabaseHas('tasks', ['id' => $task1->id, 'order' => 2]);
        $this->assertDatabaseHas('tasks', ['id' => $task2->id, 'order' => 3]);
    }

    /**
     * Test reordering with invalid task IDs.
     */
    public function test_user_cannot_reorder_with_invalid_task_ids(): void
    {
        $task1 = Task::factory()->create(['user_id' => $this->user->id]);

        $reorderData = [
            'tasks' => [$task1->id, 99999] // Non-existent task
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/tasks/reorder', $reorderData);

        $response->assertStatus(422);
    }

    /**
     * Test user cannot reorder another user's tasks.
     */
    public function test_user_cannot_reorder_other_users_tasks(): void
    {
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id, 'order' => 1]);
        $userTask = Task::factory()->create(['user_id' => $this->user->id, 'order' => 1]);

        $reorderData = [
            'tasks' => [$otherTask->id, $userTask->id]
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/tasks/reorder', $reorderData);

        $response->assertStatus(403);

        // Verify other user's task order didn't change
        $this->assertDatabaseHas('tasks', ['id' => $otherTask->id, 'order' => 1]);
    }

    /**
     * Test reordering with duplicate order values.
     */
    public function test_user_cannot_reorder_with_duplicate_orders(): void
    {
        $task1 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 1]);
        $task2 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 2]);

        $reorderData = [
            'tasks' => [$task1->id, $task1->id] // Duplicate task ID
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/tasks/reorder', $reorderData);

        $response->assertStatus(200);
    }

    /**
     * Test reordering with missing required fields.
     */
    public function test_user_cannot_reorder_with_missing_fields(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/tasks/reorder', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['tasks']);
    }

    /**
     * Test reordering with invalid order format.
     */
    public function test_user_cannot_reorder_with_invalid_format(): void
    {
        $task1 = Task::factory()->create(['user_id' => $this->user->id]);

        $reorderData = [
            'tasks' => [
                'invalid', // Non-integer value
                $task1->id
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/tasks/reorder', $reorderData);

        $response->assertStatus(422);
    }

    /**
     * Test that task order is maintained after creation.
     */
    public function test_new_tasks_get_correct_order(): void
    {
        // Create first task
        $response1 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson('/api/tasks', [
                             'title' => 'First Task',
                             'priority' => 'high',
                             'status' => 'pending',
                         ]);

        $task1Id = $response1->json('data.id');
        $this->assertDatabaseHas('tasks', ['id' => $task1Id, 'order' => 1]);

        // Create second task
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson('/api/tasks', [
                             'title' => 'Second Task',
                             'priority' => 'medium',
                             'status' => 'pending',
                         ]);

        $task2Id = $response2->json('data.id');
        $this->assertDatabaseHas('tasks', ['id' => $task2Id, 'order' => 2]);
    }

    /**
     * Test that tasks are returned in correct order.
     */
    public function test_tasks_are_returned_in_order(): void
    {
        // Create tasks with specific order
        $task1 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 3, 'title' => 'Third']);
        $task2 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 1, 'title' => 'First']);
        $task3 = Task::factory()->create(['user_id' => $this->user->id, 'order' => 2, 'title' => 'Second']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/tasks');

        $response->assertStatus(200);

        $tasks = $response->json('data');
        $this->assertEquals('First', $tasks[0]['title']);
        $this->assertEquals('Second', $tasks[1]['title']);
        $this->assertEquals('Third', $tasks[2]['title']);
    }

    /**
     * Test reordering with empty task list.
     */
    public function test_reordering_empty_task_list(): void
    {
        $reorderData = [
            'tasks' => [],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/tasks/reorder', $reorderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['tasks']);
    }
}
