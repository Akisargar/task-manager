<?php

namespace App\RulesEngine;

use App\RulesEngine\Interfaces\TaskRuleStrategyInterface;
use App\RulesEngine\Strategies\DepartmentRuleStrategy;
use App\RulesEngine\Strategies\YearsOfExperienceRuleStrategy;
use App\RulesEngine\Strategies\LocationRuleStrategy;
use App\RulesEngine\Strategies\ActiveTaskCountRuleStrategy;
use App\RulesEngine\Strategies\RoleRuleStrategy;
use InvalidArgumentException;

class TaskRuleFactory
{
    public static function make(string $field): TaskRuleStrategyInterface
    {
        return match ($field) {
            'department' => new DepartmentRuleStrategy(),
            'years_of_experience', 'experience' => new YearsOfExperienceRuleStrategy(),
            'location' => new LocationRuleStrategy(),
            'active_task_count', 'active_tasks' => new ActiveTaskCountRuleStrategy(),
            'role' => new RoleRuleStrategy(),
            default => throw new InvalidArgumentException("No rule strategy found for field: {$field}"),
        };
    }
}
