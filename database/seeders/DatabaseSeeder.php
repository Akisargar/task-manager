<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'role' => 'manager',
            'department' => 'IT',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'role' => 'user',
            'department' => 'IT',
            'years_of_experience' => 5,
        ]);

        User::factory(100)->create();

        $admin = User::where('role', 'admin')->first();

        // Create tasks with rules
        \App\Models\Task::factory(20)->create(['created_by' => $admin->id])->each(function ($task) {
            \App\Models\TaskRule::factory()->create([
                'task_id' => $task->id,
                'field' => 'department',
                'operator' => '=',
                'value' => 'IT',
            ]);
            
            \App\Models\TaskRule::factory()->create([
                'task_id' => $task->id,
                'field' => 'years_of_experience',
                'operator' => '>=',
                'value' => '2',
            ]);
        });
    }
}
