<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'field' => fake()->randomElement(['department', 'years_of_experience', 'location', 'active_task_count']),
            'operator' => fake()->randomElement(['=', '!=', '>', '>=', '<', '<=']),
            'value' => fake()->word(),
        ];
    }
}
