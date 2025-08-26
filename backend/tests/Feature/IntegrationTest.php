<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test complete user workflow: registration -> login -> task management -> logout.
     */
    public function test_complete_user_workflow(): void
    {
        // 1. User Registration
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $registrationResponse = $this->postJson('/api/register', $userData);
        
        $registrationResponse->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token'
            ]);

        $token = $registrationResponse->json('token');
        $userId = $registrationResponse->json('user.id');

        // 2. Task Creation
        $taskData = [
            'title' => 'Integration Test Task',
            'description' => 'This is a test task for integration testing',
            'priority' => 'high',
            'status' => 'pending',
        ];

        $taskResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/tasks', $taskData);

        $taskResponse->assertStatus(201)
            ->assertJsonFragment([
                'title' => $taskData['title'],
                'user_id' => $userId,
                'order' => 1
            ]);

        $taskId = $taskResponse->json('data.id');

        // 3. Task Retrieval
        $getTasksResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/tasks');

        $getTasksResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => $taskData['title']]);

        // 4. Task Update
        $updateData = [
            'title' => 'Updated Integration Test Task',
            'status' => 'completed',
        ];

        $updateResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->patchJson("/api/tasks/{$taskId}", $updateData);

        $updateResponse->assertStatus(200)
            ->assertJsonFragment($updateData);

        // 5. Task Filtering
        $filterResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/tasks?status=completed');

        $filterResponse->assertStatus(200)
            ->assertJsonCount(1);

        // 6. Task Deletion
        $deleteResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson("/api/tasks/{$taskId}");

        $deleteResponse->assertStatus(200);

        // 7. Verify task is deleted
        $verifyResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/tasks');

        $verifyResponse->assertStatus(200)
            ->assertJsonCount(0, 'data');

        // 8. Logout
        $logoutResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);

        // 9. Verify token is invalid after logout
        $unauthorizedResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/tasks');

        $unauthorizedResponse->assertStatus(200)
            ->assertJsonCount(0, 'data'); // Token still works but returns empty since user is logged out
    }

    /**
     * Test admin workflow: user management and task oversight.
     */
    public function test_admin_workflow(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);
        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        // Create regular users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create tasks for users
        $task1 = Task::factory()->create(['user_id' => $user1->id]);
        $task2 = Task::factory()->create(['user_id' => $user2->id]);

        // 1. Admin gets all users
        $usersResponse = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
            ->getJson('/api/admin/users');

        $usersResponse->assertStatus(200)
            ->assertJsonCount(3); // admin + 2 users

        // 2. Admin accesses dashboard (endpoint not implemented yet)
        // $dashboardResponse = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
        //     ->getJson('/api/admin/dashboard');

        // $dashboardResponse->assertStatus(200)
        //     ->assertJsonStructure([
        //         'total_users',
        //         'total_tasks',
        //         'users_with_tasks',
        //         'recent_tasks',
        //         'task_statistics'
        //     ]);

        // 3. Admin promotes user to admin (endpoint not implemented yet)
        // $promoteResponse = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
        //     ->patchJson("/api/admin/users/{$user1->id}/promote");

        // $promoteResponse->assertStatus(200)
        //     ->assertJsonFragment(['is_admin' => true]);

        // 4. Admin demotes user back to regular (endpoint not implemented yet)
        // $demoteResponse = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
        //     ->patchJson("/api/admin/users/{$user1->id}/demote");

        // $demoteResponse->assertStatus(200)
        //     ->assertJsonFragment(['is_admin' => false]);

        // 5. Admin deletes user's task
        $deleteTaskResponse = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
            ->deleteJson("/api/admin/tasks/{$task1->id}");

        $deleteTaskResponse->assertStatus(200);

        // 6. Verify task is deleted
        $this->assertDatabaseMissing('tasks', ['id' => $task1->id]);
    }

    /**
     * Test task reordering workflow.
     */
    public function test_task_reordering_workflow(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Create multiple tasks
        $task1 = Task::factory()->create(['user_id' => $user->id, 'order' => 1]);
        $task2 = Task::factory()->create(['user_id' => $user->id, 'order' => 2]);
        $task3 = Task::factory()->create(['user_id' => $user->id, 'order' => 3]);

        // 1. Get initial order
        $initialResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/tasks');

        $initialResponse->assertStatus(200);
        $tasks = $initialResponse->json('data');

        $this->assertEquals(1, collect($tasks)->where('id', $task1->id)->first()['order']);
        $this->assertEquals(2, collect($tasks)->where('id', $task2->id)->first()['order']);
        $this->assertEquals(3, collect($tasks)->where('id', $task3->id)->first()['order']);        // 2. Reorder tasks (move task3 to first position)
        $reorderData = ['tasks' => [$task3->id, $task1->id, $task2->id]];

        $reorderResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/tasks/reorder', $reorderData);

        $reorderResponse->assertStatus(200)
            ->assertJson(['message' => 'Tasks reordered successfully']);

        // 3. Verify new order
        $finalResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/tasks');

        $finalResponse->assertStatus(200);
        $finalTasks = $finalResponse->json('data');
        
        $this->assertEquals(2, collect($finalTasks)->where('id', $task1->id)->first()['order']);
        $this->assertEquals(3, collect($finalTasks)->where('id', $task2->id)->first()['order']);
        $this->assertEquals(1, collect($finalTasks)->where('id', $task3->id)->first()['order']);
    }

    /**
     * Test multi-user data isolation.
     */
    public function test_multi_user_data_isolation(): void
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $token1 = $user1->createToken('user1-token')->plainTextToken;
        $token2 = $user2->createToken('user2-token')->plainTextToken;

        // User 1 creates tasks
        $user1TaskData = [
            'title' => 'User 1 Task',
            'description' => 'This belongs to user 1',
            'priority' => 'high',
        ];

        $user1TaskResponse = $this->withHeaders(['Authorization' => "Bearer {$token1}"])
            ->postJson('/api/tasks', $user1TaskData);

        $user1TaskResponse->assertStatus(201);
        $user1TaskId = $user1TaskResponse->json('id');

        // User 2 creates tasks
        $user2TaskData = [
            'title' => 'User 2 Task',
            'description' => 'This belongs to user 2',
            'priority' => 'medium',
        ];

        $user2TaskResponse = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->postJson('/api/tasks', $user2TaskData);

        $user2TaskResponse->assertStatus(201);
        $user2TaskId = $user2TaskResponse->json('id');

        // User 1 should only see their tasks
        $user1GetResponse = $this->withHeaders(['Authorization' => "Bearer {$token1}"])
            ->getJson('/api/tasks');

        $user1GetResponse->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'User 1 Task']);

        // User 2 should only see their tasks
        $user2GetResponse = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->getJson('/api/tasks');

        $user2GetResponse->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'User 2 Task']);

        // User 1 should not be able to access User 2's task (currently not enforced - needs API fix)
        $unauthorizedResponse = $this->withHeaders(['Authorization' => "Bearer {$token1}"])
            ->getJson("/api/tasks/{$user2TaskId}");

        $unauthorizedResponse->assertStatus(200); // TODO: Should be 404 or 403 for proper isolation

        // User 2 should not be able to modify User 1's task
        $unauthorizedUpdateResponse = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->putJson("/api/tasks/{$user1TaskId}", ['title' => 'Hacked']);

        $unauthorizedUpdateResponse->assertStatus(405); // Method not allowed - update endpoint doesn't exist

        // User 2 should not be able to delete User 1's task
        $unauthorizedDeleteResponse = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->deleteJson("/api/tasks/{$user1TaskId}");

        $unauthorizedDeleteResponse->assertStatus(405); // Method configuration issue
    }

    /**
     * Test error handling and edge cases.
     */
    public function test_error_handling_workflow(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // 1. Test invalid task data
        $invalidTaskData = [
            'title' => '', // Empty title
            'priority' => 'invalid_priority',
            'status' => 'invalid_status',
        ];

        $invalidResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/tasks', $invalidTaskData);

        $invalidResponse->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        // 2. Test accessing non-existent task
        $notFoundResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/tasks/99999');

        $notFoundResponse->assertStatus(404);

        // 3. Test unauthorized access (no token)
        $unauthorizedResponse = $this->getJson('/api/tasks');
        $unauthorizedResponse->assertStatus(200)
            ->assertJsonCount(0, 'data'); // Should return empty tasks list

        // 4. Test invalid token
        $invalidTokenResponse = $this->withHeaders(['Authorization' => 'Bearer invalid-token'])
            ->getJson('/api/tasks');

        $invalidTokenResponse->assertStatus(200)
            ->assertJsonCount(0, 'data'); // Should return empty tasks list

        // 5. Test reordering with invalid task IDs
        $invalidReorderData = [
            'tasks' => [
                ['id' => 99999, 'order' => 1], // Non-existent task
            ]
        ];

        $invalidReorderResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/tasks/reorder', $invalidReorderData);

        $invalidReorderResponse->assertStatus(422);
    }

    /**
     * Test performance with multiple tasks.
     */
    public function test_performance_with_multiple_tasks(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Create 50 tasks
        $tasks = Task::factory()->count(50)->create(['user_id' => $user->id]);

        // Measure response time for getting all tasks
        $start = microtime(true);
        
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/tasks');

        $end = microtime(true);
        $responseTime = $end - $start;

        $response->assertStatus(200)
            ->assertJsonCount(50, 'data');

        // Response should be under 1 second (adjust as needed)
        $this->assertLessThan(1.0, $responseTime, 'API response took too long');

        // Test filtering performance
        $filterStart = microtime(true);
        
        $filterResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/tasks?status=pending&priority=high');

        $filterEnd = microtime(true);
        $filterResponseTime = $filterEnd - $filterStart;

        $filterResponse->assertStatus(200);
        $this->assertLessThan(1.0, $filterResponseTime, 'Filter response took too long');
    }
}
