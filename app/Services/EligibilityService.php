<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\RulesEngine\TaskRuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class EligibilityService
{
    /**
     * Get the query builder for users eligible for a specific task based on its rules.
     */
    public function getEligibleUsersQuery(Task $task): Builder
    {
        $query = User::query();

        $rules = $task->relationLoaded('rules') ? $task->rules : $task->rules()->get();

        if ($rules->isEmpty()) {
            return $query;
        }

        foreach ($rules as $rule) {
            try {
                $strategy = TaskRuleFactory::make($rule->field);
                $query = $strategy->apply($query, $rule);
            } catch (\Exception $e) {
                Log::error("Error applying rule {$rule->field} {$rule->operator} {$rule->value}: " . $e->getMessage());
            }
        }

        return $query;
    }

    /**
     * Get users eligible for a specific task based on its rules (alias for getEligibleUsersForTask).
     */
    public function getEligibleUsers(Task $task): Collection
    {
        return $this->getEligibleUsersForTask($task);
    }

    /**
     * Get users eligible for a specific task based on its rules.
     */
    public function getEligibleUsersForTask(Task $task): Collection
    {
        return $this->getEligibleUsersQuery($task)->get();
    }

    /**
     * Check if a specific user is eligible for a task.
     */
    public function isUserEligibleForTask(User $user, Task $task): bool
    {
        $rules = $task->relationLoaded('rules') ? $task->rules : $task->rules()->get();

        if ($rules->isEmpty()) {
            return true;
        }

        $query = User::where('id', $user->id);

        foreach ($rules as $rule) {
            try {
                $strategy = TaskRuleFactory::make($rule->field);
                $query = $strategy->apply($query, $rule);
            } catch (\Exception $e) {
                Log::error("Error applying rule in isUserEligibleForTask: " . $e->getMessage());
                return false;
            }
        }

        return $query->exists();
    }

    /**
     * Select the optimal eligible user for a task based on load-balancing and priority rules.
     * Order of selection:
     * 1. Operational staff preference (role 'user' first, then 'manager', then 'admin')
     * 2. active_task_count ASC (least loaded user first)
     * 3. years_of_experience DESC (most experienced as tiebreaker)
     * 4. id ASC (deterministic tiebreaker)
     */
    public function selectOptimalUserForTask(Task $task, array $excludeUserIds = []): ?User
    {
        $query = $this->getEligibleUsersQuery($task);

        if (!empty($excludeUserIds)) {
            $query->whereNotIn('id', $excludeUserIds);
        }

        return $query
            ->withCount('assignedTasks')
            ->orderByRaw("CASE WHEN role = 'user' THEN 1 WHEN role = 'manager' THEN 2 ELSE 3 END ASC")
            ->orderBy('active_task_count', 'asc')
            ->orderBy('assigned_tasks_count', 'asc')
            ->orderBy('years_of_experience', 'desc')
            ->orderBy('id', 'asc')
            ->first();
    }

    /**
     * Check all unassigned tasks and assign them according to priority and assignment rules.
     * 
     * Algorithm Workflow:
     * 1. Check all tasks priority first:
     *    - 'critical' (highest urgency)
     *    - 'high'
     *    - 'medium'
     *    - 'low'
     *    - Secondary: earliest due_date
     *    - Tertiary: task id ASC
     * 2. For each task in priority order:
     *    - Evaluate task rules using Rule Engine Strategies
     *    - Select single optimal eligible candidate based on load-balancing and experience
     *    - Create assignment and immediately update user's active_task_count for next task
     * 3. Sync all affected users' active task counts.
     */
    public function assignUnassignedTasks(?array $taskIds = null): array
    {
        $query = Task::unassigned()->with('rules');

        if (!empty($taskIds)) {
            $query->whereIn('id', $taskIds);
        }

        // 1. Order by task priority first, then due date, then ID
        $tasks = $query->orderByPriority('asc')->get();

        $assigned = [];
        $unassigned = [];
        $affectedUsers = [];

        foreach ($tasks as $task) {
            $optimalUser = $this->selectOptimalUserForTask($task);

            if ($optimalUser) {
                TaskAssignment::create([
                    'task_id' => $task->id,
                    'user_id' => $optimalUser->id,
                    'assigned_at' => now(),
                ]);

                // Immediately sync active task count so subsequent tasks balance properly
                $optimalUser->syncActiveTaskCount();
                $affectedUsers[$optimalUser->id] = $optimalUser;

                $assigned[] = [
                    'task_id' => $task->id,
                    'title' => $task->title,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date?->format('Y-m-d'),
                    'user_id' => $optimalUser->id,
                    'user_name' => $optimalUser->name,
                    'user_role' => $optimalUser->role,
                    'user_department' => $optimalUser->department,
                    'active_task_count' => $optimalUser->active_task_count,
                ];
            } else {
                $unassigned[] = [
                    'task_id' => $task->id,
                    'title' => $task->title,
                    'priority' => $task->priority,
                    'rules' => $task->rules->map(fn($r) => "{$r->field} {$r->operator} {$r->value}")->toArray(),
                    'reason' => 'No eligible user matches the task rules',
                ];
            }
        }

        // Final sync of active task counts on all affected users
        foreach ($affectedUsers as $user) {
            $user->syncActiveTaskCount();
        }

        return [
            'total_checked' => $tasks->count(),
            'assigned_count' => count($assigned),
            'unassigned_count' => count($unassigned),
            'assigned_tasks' => $assigned,
            'unassigned_tasks' => $unassigned,
        ];
    }
}
