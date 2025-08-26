<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminFunctionalityTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;
    private User $regularUser;
    private string $adminToken;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]);
        
        $this->adminToken = $this->adminUser->createToken('admin-token')->plainTextToken;
        $this->userToken = $this->regularUser->createToken('user-token')->plainTextToken;
    }

    /**
     * Test admin can access admin dashboard.
     */
    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_users',
                        'total_tasks',
                        'completed_tasks',
                        'high_priority_tasks',
                        'completion_rate',
                        'top_performers',
                        'priority_distribution',
                    ],
                ]);
    }

    /**
     * Test regular user cannot access admin dashboard.
     */
    public function test_regular_user_cannot_access_dashboard(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
                        ->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test admin can view all users.
     */
    public function test_admin_can_view_all_users(): void
    {
        // Create additional users
        User::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->getJson('/api/admin/users');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'is_admin',
                            'tasks_count',
                            'completed_tasks_count',
                            'pending_tasks_count',
                            'completion_rate',
                            'recent_activity',
                        ],
                    ],
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ]);
    }

    /**
     * Test regular user cannot view all users.
     */
    public function test_regular_user_cannot_view_all_users(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
                        ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    /**
     * Test admin can view user details.
     */
    public function test_admin_can_view_user_details(): void
    {
        // Create tasks for the regular user
        Task::factory()->count(3)->create(['user_id' => $this->regularUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->getJson("/api/admin/users/{$this->regularUser->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'is_admin',
                        ],
                        'statistics' => [
                            'total_tasks',
                            'completed_tasks',
                            'pending_tasks',
                            'completion_rate',
                            'high_priority_tasks',
                            'medium_priority_tasks',
                            'low_priority_tasks',
                        ],
                        'tasks' => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'title',
                                    'description',
                                    'priority',
                                    'status',
                                    'created_at',
                                    'updated_at',
                                ],
                            ],
                        ],
                    ],
                ]);
    }

    /**
     * Test admin can update user role.
     */
    public function test_admin_can_update_user_role(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->putJson("/api/admin/users/{$this->regularUser->id}/role", [
                            'is_admin' => true,
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $this->regularUser->id,
                        'is_admin' => true,
                    ],
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'is_admin' => true,
        ]);
    }

    /**
     * Test regular user cannot update user roles.
     */
    public function test_regular_user_cannot_update_user_role(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
                        ->putJson("/api/admin/users/{$this->regularUser->id}/role", [
                            'is_admin' => true,
                        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'is_admin' => false,
        ]);
    }

    /**
     * Test admin can delete any user's task.
     */
    public function test_admin_can_delete_any_users_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->deleteJson("/api/admin/tasks/{$task->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Task deleted successfully',
                ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test regular user cannot delete other users' tasks.
     */
    public function test_regular_user_cannot_delete_other_users_task_via_admin(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
                        ->deleteJson("/api/admin/tasks/{$task->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test admin can search users.
     */
    public function test_admin_can_search_users(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        User::factory()->create(['name' => 'Bob Wilson', 'email' => 'bob@example.com']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->getJson('/api/admin/users?search=john');

        $response->assertStatus(200);

        $users = $response->json('data');
        $this->assertCount(1, $users);
        $this->assertEquals('John Doe', $users[0]['name']);
    }

    /**
     * Test admin can filter users by role.
     */
    public function test_admin_can_filter_users_by_role(): void
    {
        User::factory()->count(2)->create(['is_admin' => true]);
        User::factory()->count(3)->create(['is_admin' => false]);

        // Filter for admins only
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->getJson('/api/admin/users?role=admin');

        $response->assertStatus(200);

        $users = $response->json('data');
        // Should have 3 admins (2 created + 1 in setUp)
        $this->assertCount(3, $users);
        foreach ($users as $user) {
            $this->assertTrue($user['is_admin']);
        }
    }

    /**
     * Test admin dashboard statistics are accurate.
     */
    public function test_admin_dashboard_statistics_accuracy(): void
    {
        // Create test data
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Task::factory()->count(3)->create(['user_id' => $user1->id, 'status' => 'completed']);
        Task::factory()->count(2)->create(['user_id' => $user1->id, 'status' => 'pending']);
        Task::factory()->count(4)->create(['user_id' => $user2->id, 'status' => 'completed', 'priority' => 'high']);
        Task::factory()->count(1)->create(['user_id' => $user2->id, 'status' => 'pending', 'priority' => 'high']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->getJson('/api/admin/dashboard');

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Total users: admin + regular + user1 + user2 = 4
        $this->assertEquals(4, $data['total_users']);
        
        // Total tasks: 3 + 2 + 4 + 1 = 10
        $this->assertEquals(10, $data['total_tasks']);
        
        // Completed tasks: 3 + 4 = 7
        $this->assertEquals(7, $data['completed_tasks']);
        
        // High priority tasks: 4 + 1 = 5
        $this->assertEquals(5, $data['high_priority_tasks']);
    }

    /**
     * Test admin cannot modify their own role.
     */
    public function test_admin_cannot_modify_own_role(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->putJson("/api/admin/users/{$this->adminUser->id}/role", [
                            'is_admin' => false,
                        ]);

        $response->assertStatus(403)
                ->assertJson([
                    'message' => 'You cannot modify your own role',
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
            'is_admin' => true,
        ]);
    }

    /**
     * Test unauthenticated user cannot access admin routes.
     */
    public function test_unauthenticated_user_cannot_access_admin_routes(): void
    {
        $routes = [
            '/api/admin/dashboard',
            '/api/admin/users',
            "/api/admin/users/{$this->regularUser->id}",
        ];

        foreach ($routes as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(401);
        }
    }
}
