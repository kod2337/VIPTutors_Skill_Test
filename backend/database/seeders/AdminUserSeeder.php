<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@viptutors.com'],
            [
                'name' => 'VIP Admin',
                'email' => 'admin@viptutors.com',
                'password' => Hash::make('admin123'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Also create a regular test user if needed
        User::firstOrCreate(
            ['email' => 'user@viptutors.com'],
            [
                'name' => 'Test User',
                'email' => 'user@viptutors.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created:');
        $this->command->info('Email: admin@viptutors.com');
        $this->command->info('Password: admin123');
        $this->command->info('');
        $this->command->info('Test user created:');
        $this->command->info('Email: user@viptutors.com');
        $this->command->info('Password: password');
    }
}
