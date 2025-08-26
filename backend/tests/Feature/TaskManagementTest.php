<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskManagementTest extends TestCase
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
     * Test creating a task with valid data.
     */
    public function test_authenticated_user_can_create_task(): void
    {
        $taskData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'priority' => 'high',
            'status' => 'pending',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'title',
                        'description',
                        'priority',
                        'status',
                        'order',
                        'user_id',
                        'created_at',
                        'updated_at',
                    ],
                ]);

        $this->assertDatabaseHas('tasks', [
            'title' => $taskData['title'],
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test creating a task with invalid data.
     */
    public function test_user_cannot_create_task_with_invalid_data(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/tasks', [
                            'title' => '',
                            'priority' => 'invalid',
                            'status' => 'invalid',
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'priority', 'status']);
    }

    /**
     * Test retrieving user's tasks.
     */
    public function test_user_can_retrieve_their_tasks(): void
    {
        // Create tasks for this user
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        
        // Create tasks for another user (should not be returned)
        $otherUser = User::factory()->create();
        Task::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/tasks');

        $response->assertStatus(200)
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'priority',
                            'status',
                            'order',
                            'user_id',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ]);

        // Verify all returned tasks belong to the authenticated user
        $taskUserIds = collect($response->json('data'))->pluck('user_id')->unique();
        $this->assertEquals([$this->user->id], $taskUserIds->toArray());
    }

    /**
     * Test retrieving a specific task.
     */
    public function test_user_can_retrieve_specific_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $task->id,
                        'title' => $task->title,
                        'user_id' => $this->user->id,
                    ],
                ]);
    }

    /**
     * Test user cannot access another user's task.
     */
    public function test_user_cannot_access_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson("/api/tasks/{$otherTask->id}");

        $response->assertStatus(403);
    }

    /**
     * Test updating a task.
     */
    public function test_user_can_update_their_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        $updateData = [
            'title' => 'Updated Task Title',
            'description' => 'Updated description',
            'priority' => 'low',
            'status' => 'completed',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $task->id,
                        'title' => $updateData['title'],
                        'description' => $updateData['description'],
                        'priority' => $updateData['priority'],
                        'status' => $updateData['status'],
                    ],
                ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => $updateData['title'],
        ]);
    }

    /**
     * Test user cannot update another user's task.
     */
    public function test_user_cannot_update_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->putJson("/api/tasks/{$otherTask->id}", [
                            'title' => 'Hacked Title',
                        ]);

        $response->assertStatus(403);
    }

    /**
     * Test deleting a task.
     */
    public function test_user_can_delete_their_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Task deleted successfully',
                ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test user cannot delete another user's task.
     */
    public function test_user_cannot_delete_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->deleteJson("/api/tasks/{$otherTask->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('tasks', [
            'id' => $otherTask->id,
        ]);
    }

    /**
     * Test task filtering by status.
     */
    public function test_user_can_filter_tasks_by_status(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'completed']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/tasks?status=pending');

        $response->assertStatus(200)
                ->assertJsonCount(2, 'data');

        $statuses = collect($response->json('data'))->pluck('status')->unique();
        $this->assertEquals(['pending'], $statuses->toArray());
    }

    /**
     * Test task filtering by priority.
     */
    public function test_user_can_filter_tasks_by_priority(): void
    {
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'high']);
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'low']);
        Task::factory()->create(['user_id' => $this->user->id, 'priority' => 'high']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/tasks?priority=high');

        $response->assertStatus(200)
                ->assertJsonCount(2, 'data');

        $priorities = collect($response->json('data'))->pluck('priority')->unique();
        $this->assertEquals(['high'], $priorities->toArray());
    }

    /**
     * Test unauthenticated user cannot access task endpoints.
     */
    public function test_unauthenticated_user_cannot_access_tasks(): void
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'priority' => 'high',
            'status' => 'pending',
        ]);
        $response->assertStatus(401);
    }
}
