<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsolationDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_isolation_debug(): void
    {
        // Create two users and login them
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $login1Response = $this->postJson('/api/login', [
            'email' => $user1->email,
            'password' => 'password'
        ]);
        $token1 = $login1Response->json('access_token');

        $login2Response = $this->postJson('/api/login', [
            'email' => $user2->email,
            'password' => 'password'
        ]);
        $token2 = $login2Response->json('access_token');

        // User 1 creates a task
        $task1Data = ['title' => 'User 1 Task', 'priority' => 'high'];
        $user1TaskResponse = $this->withHeaders(['Authorization' => "Bearer {$token1}"])
            ->postJson('/api/tasks', $task1Data);

        $user1TaskId = $user1TaskResponse->json('data.id');
        
        echo "\nUser1 ID: " . $user1->id;
        echo "\nUser2 ID: " . $user2->id;
        echo "\nTask ID from API: " . $user1TaskId;
        
        // Check the actual task in database
        $taskFromDb = Task::find($user1TaskId);
        if ($taskFromDb) {
            echo "\nTask user_id in DB: " . $taskFromDb->user_id;
            echo "\nTask title in DB: " . $taskFromDb->title;
        } else {
            echo "\nTask not found in database!";
        }

        // Try to access with user2's token
        $response = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->getJson("/api/tasks/{$user1TaskId}");

        echo "\nResponse status: " . $response->getStatusCode();
        echo "\nResponse content: " . $response->getContent();

        // Check what auth()->user() returns with token2
        $userResponse = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->getJson('/api/user');
        echo "\nAuth user with token2: " . $userResponse->getContent();

        $this->assertTrue(true);
    }
}
