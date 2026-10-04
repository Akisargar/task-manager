<?php

namespace App\RulesEngine\Strategies;

use App\Models\TaskRule;
use App\RulesEngine\Interfaces\TaskRuleStrategyInterface;
use Illuminate\Database\Eloquent\Builder;

class DepartmentRuleStrategy implements TaskRuleStrategyInterface
{
    public function apply(Builder $query, TaskRule $rule): Builder
    {
        if (in_array($rule->operator, ['in', 'not_in'])) {
            $values = is_array($rule->value)
                ? $rule->value
                : array_map('trim', explode(',', (string) $rule->value));
            return $rule->operator === 'in' 
                ? $query->whereIn('department', $values) 
                : $query->whereNotIn('department', $values);
        }

        return $query->where('department', $rule->operator, trim((string) $rule->value));
    }
}
