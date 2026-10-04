<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Services\EligibilityService;

class ProcessAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:process-assignments {--unassigned-only : Process only unassigned tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluate and process task assignments ordered by priority and assignment rules';

    /**
     * Execute the console command.
     */
    public function handle(EligibilityService $eligibilityService)
    {
        $unassignedOnly = $this->option('unassigned-only');

        $this->info($unassignedOnly 
            ? 'Checking all unassigned tasks (priority-first algorithm)...' 
            : 'Processing task assignments (priority-first algorithm)...'
        );

        $result = $eligibilityService->assignUnassignedTasks();

        $this->info("Total checked tasks: {$result['total_checked']}");
        $this->info("Successfully assigned: {$result['assigned_count']}");

        if ($result['unassigned_count'] > 0) {
            $this->warn("Remaining unassigned (no matching rules): {$result['unassigned_count']}");
        }

        if (!empty($result['assigned_tasks'])) {
            $rows = array_map(function ($item) {
                return [
                    $item['task_id'],
                    \Illuminate\Support\Str::limit($item['title'], 30),
                    strtoupper($item['priority']),
                    $item['user_id'],
                    $item['user_name'],
                    $item['user_department'] ?? 'N/A',
                    $item['active_task_count'],
                ];
            }, array_slice($result['assigned_tasks'], 0, 15));

            $this->table(
                ['Task ID', 'Title', 'Priority', 'User ID', 'Assigned To', 'Dept', 'Active Tasks'],
                $rows
            );

            if (count($result['assigned_tasks']) > 15) {
                $this->info('... and ' . (count($result['assigned_tasks']) - 15) . ' more tasks assigned.');
            }
        }

        return Command::SUCCESS;
    }
}
