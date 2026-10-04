<?php

namespace App\RulesEngine\Strategies;

use App\Models\TaskRule;
use App\RulesEngine\Interfaces\TaskRuleStrategyInterface;
use Illuminate\Database\Eloquent\Builder;

class YearsOfExperienceRuleStrategy implements TaskRuleStrategyInterface
{
    public function apply(Builder $query, TaskRule $rule): Builder
    {
        return $query->where('years_of_experience', $rule->operator, (int) $rule->value);
    }
}
