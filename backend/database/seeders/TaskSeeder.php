<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }
        
        $taskTemplates = [
            [
                'title' => 'Complete project proposal',
                'description' => 'Write and finalize the project proposal for the new client',
                'priority' => 'high',
                'status' => 'pending',
            ],
            [
                'title' => 'Review team code submissions',
                'description' => 'Review and provide feedback on team member code submissions',
                'priority' => 'medium',
                'status' => 'pending',
            ],
            [
                'title' => 'Update documentation',
                'description' => 'Update project documentation with latest changes',
                'priority' => 'low',
                'status' => 'completed',
            ],
            [
                'title' => 'Prepare client presentation',
                'description' => 'Create slides and demo for upcoming client meeting',
                'priority' => 'medium',
                'status' => 'pending',
            ],
            [
                'title' => 'Fix reported bugs',
                'description' => 'Address bugs reported in the latest testing cycle',
                'priority' => 'high',
                'status' => 'pending',
            ],
            [
                'title' => 'Setup development environment',
                'description' => 'Configure development environment for new team member',
                'priority' => 'medium',
                'status' => 'completed',
            ],
            [
                'title' => 'Plan sprint activities',
                'description' => 'Plan and organize activities for the next sprint',
                'priority' => 'medium',
                'status' => 'pending',
            ],
            [
                'title' => 'Research new technologies',
                'description' => 'Research emerging technologies for potential adoption',
                'priority' => 'low',
                'status' => 'pending',
            ],
        ];
        
        foreach ($users as $user) {
            // Create 4-6 tasks per user
            $numTasks = rand(4, 6);
            $selectedTasks = collect($taskTemplates)->random($numTasks);
            
            foreach ($selectedTasks as $index => $taskData) {
                Task::create([
                    'title' => $taskData['title'],
                    'description' => $taskData['description'],
                    'status' => $taskData['status'],
                    'priority' => $taskData['priority'],
                    'order' => $index + 1,
                    'user_id' => $user->id,
                ]);
            }
        }
        
        $this->command->info('Task seeder completed successfully!');
    }
}
