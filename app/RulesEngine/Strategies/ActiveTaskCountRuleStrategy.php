<?php

namespace App\RulesEngine\Strategies;

use App\Models\TaskRule;
use App\RulesEngine\Interfaces\TaskRuleStrategyInterface;
use Illuminate\Database\Eloquent\Builder;

class ActiveTaskCountRuleStrategy implements TaskRuleStrategyInterface
{
    public function apply(Builder $query, TaskRule $rule): Builder
    {
        return $query->where('active_task_count', $rule->operator, (int) $rule->value);
    }
}
