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
                'description' => 'Write and finalize the project proposal for the new client meeting next week',
                'priority' => 'high',
                'status' => 'pending',
            ],
            [
                'title' => 'Review team code submissions',
                'description' => 'Review and provide feedback on team member code submissions for the authentication module',
                'priority' => 'medium',
                'status' => 'pending',
            ],
            [
                'title' => 'Update documentation',
                'description' => 'Update project documentation with latest API changes and deployment instructions',
                'priority' => 'low',
                'status' => 'completed',
            ],
            [
                'title' => 'Prepare client presentation',
                'description' => 'Create slides and demo for upcoming client meeting about the new features',
                'priority' => 'high',
                'status' => 'pending',
            ],
            [
                'title' => 'Fix reported bugs',
                'description' => 'Address critical bugs reported in the latest testing cycle by QA team',
                'priority' => 'high',
                'status' => 'pending',
            ],
            [
                'title' => 'Setup development environment',
                'description' => 'Configure development environment for new team member joining next month',
                'priority' => 'medium',
                'status' => 'completed',
            ],
            [
                'title' => 'Plan sprint activities',
                'description' => 'Plan and organize activities for the next sprint including user stories and tasks',
                'priority' => 'medium',
                'status' => 'pending',
            ],
            [
                'title' => 'Research new technologies',
                'description' => 'Research emerging technologies like AI and machine learning for potential adoption',
                'priority' => 'low',
                'status' => 'pending',
            ],
            [
                'title' => 'Database optimization',
                'description' => 'Optimize database queries and add proper indexing for better performance',
                'priority' => 'medium',
                'status' => 'pending',
            ],
            [
                'title' => 'Security audit',
                'description' => 'Conduct comprehensive security audit of the application and fix vulnerabilities',
                'priority' => 'high',
                'status' => 'completed',
            ],
            [
                'title' => 'User interface design',
                'description' => 'Design new user interface components for the dashboard and admin panel',
                'priority' => 'medium',
                'status' => 'pending',
            ],
            [
                'title' => 'API testing automation',
                'description' => 'Create automated tests for all API endpoints using Postman or similar tools',
                'priority' => 'low',
                'status' => 'pending',
            ],
            [
                'title' => 'Mobile app integration',
                'description' => 'Integrate the web application with the mobile app using shared API endpoints',
                'priority' => 'high',
                'status' => 'pending',
            ],
            [
                'title' => 'Performance monitoring setup',
                'description' => 'Setup performance monitoring tools and alerts for production environment',
                'priority' => 'medium',
                'status' => 'completed',
            ],
            [
                'title' => 'Email notifications system',
                'description' => 'Implement email notification system for task updates and reminders',
                'priority' => 'low',
                'status' => 'pending',
            ],
            [
                'title' => 'Backup strategy implementation',
                'description' => 'Implement automated backup strategy for database and file storage',
                'priority' => 'high',
                'status' => 'completed',
            ],
            [
                'title' => 'Code refactoring',
                'description' => 'Refactor legacy code to improve maintainability and follow best practices',
                'priority' => 'low',
                'status' => 'pending',
            ],
            [
                'title' => 'Deploy to staging',
                'description' => 'Deploy latest changes to staging environment for client review and testing',
                'priority' => 'medium',
                'status' => 'completed',
            ],
            [
                'title' => 'Create user training materials',
                'description' => 'Create comprehensive training materials and video tutorials for end users',
                'priority' => 'low',
                'status' => 'pending',
            ],
            [
                'title' => 'Implement dark mode',
                'description' => 'Implement dark mode theme option for better user experience and accessibility',
                'priority' => 'medium',
                'status' => 'pending',
            ],
        ];
        
        foreach ($users as $user) {
            // Create 8-12 tasks per user for better testing
            $numTasks = rand(8, 12);
            $selectedTasks = collect($taskTemplates)->shuffle()->take($numTasks);
            
            foreach ($selectedTasks as $index => $taskData) {
                // Add some randomization to make data more realistic
                $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 23));
                $updatedAt = $createdAt->copy()->addHours(rand(0, 72));
                
                // Randomize some priorities and statuses for variety
                $priorities = ['low', 'medium', 'high'];
                $statuses = ['pending', 'completed'];
                
                // 70% chance to keep original priority, 30% to randomize
                $priority = rand(1, 100) <= 70 ? $taskData['priority'] : $priorities[array_rand($priorities)];
                
                // 80% chance to keep original status, 20% to randomize
                $status = rand(1, 100) <= 80 ? $taskData['status'] : $statuses[array_rand($statuses)];
                
                Task::create([
                    'title' => $taskData['title'],
                    'description' => $taskData['description'],
                    'status' => $status,
                    'priority' => $priority,
                    'order' => $index + 1,
                    'user_id' => $user->id,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
            }
        }
        
        $totalTasks = Task::count();
        $this->command->info("Task seeder completed successfully! Created {$totalTasks} tasks across {$users->count()} users.");
    }
}
