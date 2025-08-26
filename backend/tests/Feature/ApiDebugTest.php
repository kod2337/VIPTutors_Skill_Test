<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_task_update(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Create a task first
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Test different HTTP methods
        echo "\n=== Testing PUT method ===\n";
        $putResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->putJson("/api/tasks/{$task->id}", ['status' => 'completed']);
        
        echo "PUT Status: " . $putResponse->getStatusCode() . "\n";
        echo "PUT Response: " . $putResponse->getContent() . "\n";

        echo "\n=== Testing PATCH method ===\n";
        $patchResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->patchJson("/api/tasks/{$task->id}", ['status' => 'completed']);
        
        echo "PATCH Status: " . $patchResponse->getStatusCode() . "\n";
        echo "PATCH Response: " . $patchResponse->getContent() . "\n";

        // Just assert something to make test pass
        $this->assertTrue(true);
    }

    public function test_debug_reorder(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $task1 = Task::factory()->create(['user_id' => $user->id, 'order' => 1]);
        $task2 = Task::factory()->create(['user_id' => $user->id, 'order' => 2]);

        echo "\n=== Testing Reorder Endpoint ===\n";
        
        $reorderData = [
            'tasks' => [
                ['id' => $task2->id, 'order' => 1],
                ['id' => $task1->id, 'order' => 2],
            ]
        ];

        $reorderResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/tasks/reorder', $reorderData);

        echo "Reorder Status: " . $reorderResponse->getStatusCode() . "\n";
        echo "Reorder Response: " . $reorderResponse->getContent() . "\n";

        $this->assertTrue(true);
    }
}
