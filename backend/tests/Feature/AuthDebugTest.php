<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_auth_flow(): void
    {
        // Test registration
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        echo "\n=== Testing Registration ===\n";
        $registerResponse = $this->postJson('/api/register', $userData);
        echo "Register Status: " . $registerResponse->getStatusCode() . "\n";
        echo "Register Response: " . $registerResponse->getContent() . "\n";

        // Test login
        echo "\n=== Testing Login ===\n";
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $loginResponse = $this->postJson('/api/login', $loginData);
        echo "Login Status: " . $loginResponse->getStatusCode() . "\n";
        echo "Login Response: " . $loginResponse->getContent() . "\n";

        if ($loginResponse->getStatusCode() === 200) {
            $data = $loginResponse->json();
            $token = $data['token'] ?? null;
            
            if ($token) {
                echo "\n=== Testing Protected Route ===\n";
                $userResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                    ->getJson('/api/user');
                echo "User Status: " . $userResponse->getStatusCode() . "\n";
                echo "User Response: " . $userResponse->getContent() . "\n";

                echo "\n=== Testing Logout ===\n";
                $logoutResponse = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                    ->postJson('/api/logout');
                echo "Logout Status: " . $logoutResponse->getStatusCode() . "\n";
                echo "Logout Response: " . $logoutResponse->getContent() . "\n";
            }
        }

        $this->assertTrue(true);
    }
}
