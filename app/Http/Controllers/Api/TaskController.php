<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'priority', 'assignment_status', 'sort_by', 'order']);
        $perPage = (int) $request->input('per_page', 12);
        $tasks = $this->taskService->getAllTasks($filters, $perPage);

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,done',
            'priority' => 'nullable|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
            'rules' => 'nullable|array',
            'rules.*.field' => 'required_with:rules|string|in:department,years_of_experience,location,active_task_count,role',
            'rules.*.operator' => 'required_with:rules|string|in:=,!=,>,>=,<,<=,in,not_in',
            'rules.*.value' => 'required_with:rules|string',
        ]);

        if (isset($validated['due_date'])) {
            $validated['due_date'] = !empty($validated['due_date'])
                ? \Carbon\Carbon::parse($validated['due_date'])->format('Y-m-d')
                : null;
        }

        $taskData = collect($validated)->except('rules')->toArray();
        $rulesData = $validated['rules'] ?? [];

        $task = $this->taskService->createTask($taskData, $rulesData, $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $task->load('rules'),
            'message' => 'Task created successfully'
        ], 201);
    }

    public function show(int $id)
    {
        $task = $this->taskService->getTask($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:todo,in_progress,done',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
            'rules' => 'nullable|array',
            'rules.*.field' => 'required_with:rules|string|in:department,years_of_experience,location,active_task_count,role',
            'rules.*.operator' => 'required_with:rules|string|in:=,!=,>,>=,<,<=,in,not_in',
            'rules.*.value' => 'required_with:rules|string',
        ]);

        if (isset($validated['due_date'])) {
            $validated['due_date'] = !empty($validated['due_date'])
                ? \Carbon\Carbon::parse($validated['due_date'])->format('Y-m-d')
                : null;
        }

        $taskData = collect($validated)->except('rules')->toArray();
        $rulesData = isset($validated['rules']) ? $validated['rules'] : null;

        $updated = $this->taskService->updateTask($id, $taskData, $rulesData);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or update failed'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->taskService->getTask($id),
            'message' => 'Task updated successfully'
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,done'
        ]);

        $task = \App\Models\Task::with('assignedUsers')->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or update failed'
            ], 404);
        }

        $task->update(['status' => $validated['status']]);

        foreach ($task->assignedUsers as $user) {
            $user->syncActiveTaskCount();
        }

        return response()->json([
            'success' => true,
            'message' => 'Task status updated'
        ]);
    }

    public function destroy(int $id)
    {
        $deleted = $this->taskService->deleteTask($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or delete failed'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }
}
