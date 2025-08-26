<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_debug(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        echo "\nUser1 is_admin: " . ($user1->is_admin ? 'true' : 'false');
        echo "\nUser2 is_admin: " . ($user2->is_admin ? 'true' : 'false');

        // Create a task for user1
        $task = Task::factory()->create(['user_id' => $user1->id]);
        
        echo "\nTask user_id: " . $task->user_id;
        echo "\nUser1 ID: " . $user1->id;
        echo "\nUser2 ID: " . $user2->id;

        // Test the service method directly
        $taskService = app(\App\Services\TaskService::class);
        $canUser2Access = $taskService->canAccessTask($user2, $task);
        
        echo "\nCan User2 access User1's task: " . ($canUser2Access ? 'true' : 'false');

        $this->assertTrue(true);
    }
}
