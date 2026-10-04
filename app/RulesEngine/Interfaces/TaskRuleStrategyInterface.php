<?php

namespace App\RulesEngine\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use App\Models\TaskRule;

interface TaskRuleStrategyInterface
{
    /**
     * Apply the rule to the User query builder.
     */
    public function apply(Builder $query, TaskRule $rule): Builder;
}
