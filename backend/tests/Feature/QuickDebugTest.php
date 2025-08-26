<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_debug(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $token1 = $user1->createToken('user1-token')->plainTextToken;
        $token2 = $user2->createToken('user2-token')->plainTextToken;

        // User 1 creates a task
        $task1Data = ['title' => 'User 1 Task', 'priority' => 'high'];
        $user1TaskResponse = $this->withHeaders(['Authorization' => "Bearer {$token1}"])
            ->postJson('/api/tasks', $task1Data);

        $user1TaskId = $user1TaskResponse->json('data.id');

        // Try to access user1's task as user2
        $response = $this->withHeaders(['Authorization' => "Bearer {$token2}"])
            ->getJson("/api/tasks/{$user1TaskId}");

        echo "\nActual Status Code: " . $response->getStatusCode();
        
        $this->assertTrue(true);
    }
}
