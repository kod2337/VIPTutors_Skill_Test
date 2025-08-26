<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_debug(): void
    {
        $user = User::factory()->create();
        
        // Login user
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        $token = $loginResponse->json('access_token');

        // Create task
        $createResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/tasks', ['title' => 'Test Task', 'priority' => 'high']);

        $taskId = $createResponse->json('data.id');
        echo "\nCreated task ID: " . $taskId;

        // Verify task exists
        $beforeDelete = Task::count();
        echo "\nTasks before delete: " . $beforeDelete;

        // Delete task
        $deleteResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson("/api/tasks/{$taskId}");

        echo "\nDelete response status: " . $deleteResponse->getStatusCode();
        echo "\nDelete response: " . $deleteResponse->getContent();

        // Verify task is deleted
        $afterDelete = Task::count();
        echo "\nTasks after delete: " . $afterDelete;
        
        $task = Task::find($taskId);
        echo "\nTask still exists: " . ($task ? 'yes' : 'no');

        $this->assertTrue(true);
    }
}
