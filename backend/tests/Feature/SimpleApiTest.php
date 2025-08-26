<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_simple_api_calls(): void
    {
        $user = User::factory()->create();

        // Login
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $token = $loginResponse->json('access_token');
        
        echo "\n=== LOGIN ===";
        echo "\nStatus: " . $loginResponse->getStatusCode();
        echo "\nToken: " . substr($token, 0, 20) . "...";

        // Create task
        $createResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/tasks', ['title' => 'Test Task', 'priority' => 'high']);

        echo "\n\n=== CREATE ===";
        echo "\nStatus: " . $createResponse->getStatusCode();
        echo "\nResponse: " . $createResponse->getContent();
        
        $taskId = $createResponse->json('id');

        // Try PUT update
        echo "\n\n=== PUT UPDATE ===";
        $putResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->putJson("/api/tasks/{$taskId}", ['status' => 'completed']);
        echo "\nStatus: " . $putResponse->getStatusCode();
        echo "\nResponse: " . $putResponse->getContent();

        // Try PATCH update  
        echo "\n\n=== PATCH UPDATE ===";
        $patchResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->patchJson("/api/tasks/{$taskId}", ['status' => 'pending']);
        echo "\nStatus: " . $patchResponse->getStatusCode();
        echo "\nResponse: " . $patchResponse->getContent();

        $this->assertTrue(true);
    }
}
