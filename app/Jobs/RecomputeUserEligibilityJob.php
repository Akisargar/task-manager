<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Task;
use App\Services\EligibilityService;
use App\Models\TaskAssignment;

class RecomputeUserEligibilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;

    /**
     * Create a new job instance. Accepts either a User model or an integer ID.
     */
    public function __construct(User|int $user)
    {
        $this->user = $user instanceof User ? $user : User::findOrFail($user);
    }

    /**
     * Execute the job.
     */
    public function handle(EligibilityService $eligibilityService): void
    {
        // 1. Re-evaluate tasks currently assigned to this user that are active (todo or in_progress)
        $assignedTasks = $this->user->assignedTasks()
            ->whereIn('tasks.status', ['todo', 'in_progress'])
            ->with('rules')
            ->get();

        foreach ($assignedTasks as $task) {
            if (!$eligibilityService->isUserEligibleForTask($this->user, $task)) {
                // User is no longer eligible! Remove assignment and reassign to new optimal candidate
                TaskAssignment::where('task_id', $task->id)
                    ->where('user_id', $this->user->id)
                    ->delete();

                $newOptimalUser = $eligibilityService->selectOptimalUserForTask($task, [$this->user->id]);
                if ($newOptimalUser) {
                    TaskAssignment::create([
                        'task_id' => $task->id,
                        'user_id' => $newOptimalUser->id,
                        'assigned_at' => now(),
                    ]);
                    $newOptimalUser->syncActiveTaskCount();
                }
            }
        }

        // 2. Check unassigned active tasks: if this user is eligible, assign if optimal
        $unassignedTasks = Task::whereIn('status', ['todo', 'in_progress'])
            ->doesntHave('assignments')
            ->with('rules')
            ->get();

        foreach ($unassignedTasks as $task) {
            if ($eligibilityService->isUserEligibleForTask($this->user, $task)) {
                $optimalUser = $eligibilityService->selectOptimalUserForTask($task);
                if ($optimalUser) {
                    TaskAssignment::create([
                        'task_id' => $task->id,
                        'user_id' => $optimalUser->id,
                        'assigned_at' => now(),
                    ]);
                    $optimalUser->syncActiveTaskCount();
                }
            }
        }

        $this->user->syncActiveTaskCount();
    }
}
