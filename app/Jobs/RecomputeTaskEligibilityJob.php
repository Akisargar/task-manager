<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;
use App\Models\User;
use App\Services\EligibilityService;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecomputeTaskEligibilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Task $task;

    /**
     * Create a new job instance. Accepts either a Task model or an integer ID.
     */
    public function __construct(Task|int $task)
    {
        $this->task = $task instanceof Task ? $task : Task::with('rules')->findOrFail($task);
    }

    /**
     * Execute the job.
     */
    public function handle(EligibilityService $eligibilityService): void
    {
        $currentAssignees = $this->task->assignedUsers()->get();
        $previousUserIds = $currentAssignees->pluck('id')->toArray();

        // If currently assigned to exactly 1 user and that user is still eligible, keep them
        if ($currentAssignees->count() === 1) {
            $currentUser = $currentAssignees->first();
            if ($eligibilityService->isUserEligibleForTask($currentUser, $this->task)) {
                $currentUser->syncActiveTaskCount();
                return;
            }
        }

        // Select the single optimal user based on rules and load balancing
        $optimalUser = $eligibilityService->selectOptimalUserForTask($this->task);

        DB::transaction(function () use ($optimalUser) {
            TaskAssignment::where('task_id', $this->task->id)->delete();

            if ($optimalUser) {
                TaskAssignment::create([
                    'task_id' => $this->task->id,
                    'user_id' => $optimalUser->id,
                    'assigned_at' => now(),
                ]);
            }
        });

        if (!$optimalUser) {
            Log::info("No eligible user found during recomputation for Task #{$this->task->id}");
        }

        // Sync active_task_count for all affected users
        $affectedUserIds = array_unique(array_filter(array_merge($previousUserIds, [$optimalUser?->id])));
        if (!empty($affectedUserIds)) {
            foreach (User::whereIn('id', $affectedUserIds)->get() as $user) {
                $user->syncActiveTaskCount();
            }
        }
    }
}
