<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\EligibilityService;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(private EligibilityService $eligibilityService) {}

    public function eligibleUsers(int $id)
    {
        $task = Task::with('rules')->findOrFail($id);
        $users = $this->eligibilityService->getEligibleUsersForTask($task);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function myTasks(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->input('per_page', 50);
        
        $tasks = Task::whereHas('assignments', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with([
            'rules',
            'assignedUsers:id,name,email,role,department',
            'creator:id,name,email'
        ])
        ->orderByRaw("CASE status WHEN 'in_progress' THEN 1 WHEN 'todo' THEN 2 WHEN 'done' THEN 3 ELSE 4 END ASC")
        ->orderByPriority('asc')
        ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tasks->items(),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ]
        ]);
    }

    public function assignUnassigned(Request $request)
    {
        $taskIds = $request->input('task_ids');
        $result = $this->eligibilityService->assignUnassignedTasks($taskIds);

        return response()->json([
            'success' => true,
            'message' => "Processed {$result['total_checked']} unassigned tasks; assigned {$result['assigned_count']} tasks based on priority and rules.",
            'data' => $result
        ]);
    }

    public function recompute(Request $request)
    {
        $tasks = Task::with('rules')
            ->whereIn('status', ['todo', 'in_progress'])
            ->orderByPriority('asc')
            ->get();

        foreach ($tasks as $task) {
            \App\Jobs\RecomputeTaskEligibilityJob::dispatch($task);
        }

        return response()->json([
            'success' => true,
            'message' => 'Eligibility recomputation jobs dispatched successfully in priority order.'
        ]);
    }
}
