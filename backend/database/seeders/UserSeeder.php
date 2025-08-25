<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@viptutors.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Create test users
        User::create([
            'name' => 'John Smith',
            'email' => 'john@viptutors.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah@viptutors.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        User::create([
            'name' => 'Mike Wilson',
            'email' => 'mike@viptutors.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        User::create([
            'name' => 'Emma Davis',
            'email' => 'emma@viptutors.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        $this->command->info('User seeder completed successfully! Created 5 users (1 admin, 4 regular users).');
    }
}
