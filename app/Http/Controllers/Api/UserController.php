<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\User::query();

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 12);
        $users = $query->withCount(['assignedTasks', 'createdTasks'])
                       ->latest('id')
                       ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    public function show($id)
    {
        $user = \App\Models\User::with(['assignedTasks' => function ($q) {
            $q->select('tasks.id', 'tasks.title', 'tasks.priority', 'tasks.status', 'tasks.due_date', 'tasks.created_by')
              ->latest('task_assignments.assigned_at');
        }, 'createdTasks' => function ($q) {
            $q->select('id', 'title', 'priority', 'status', 'created_by', 'created_at')
              ->latest('id')
              ->take(10);
        }])->withCount(['assignedTasks', 'createdTasks'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,manager,user',
            'department' => 'nullable|in:Finance,HR,IT,Operation',
            'years_of_experience' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        $validated['years_of_experience'] = (int) ($validated['years_of_experience'] ?? 0);
        $validated['active_task_count'] = 0;

        $user = \App\Models\User::create($validated);

        \App\Jobs\RecomputeUserEligibilityJob::dispatch($user);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User created successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|nullable|string|max:255',
            'role' => 'sometimes|in:admin,manager,user',
            'department' => 'nullable|string|in:Finance,HR,IT,Operation',
            'years_of_experience' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $eligibilityChanged = false;
        foreach (['department', 'years_of_experience', 'location'] as $attribute) {
            if (isset($validated[$attribute]) && $user->{$attribute} !== $validated[$attribute]) {
                $eligibilityChanged = true;
                break;
            }
        }

        $user->update($validated);

        if ($eligibilityChanged) {
            \App\Jobs\RecomputeUserEligibilityJob::dispatch($user);
        }

        return response()->json([
            'success' => true,
            'data' => $user->fresh(),
            'message' => 'User updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $user = \App\Models\User::findOrFail($id);

        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|nullable|string|max:255',
            'department' => 'nullable|string|in:Finance,HR,IT,Operation',
            'years_of_experience' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        // Check if any attributes relevant to task eligibility changed
        $eligibilityChanged = false;
        foreach (['department', 'years_of_experience', 'location'] as $attribute) {
            if (isset($validated[$attribute]) && $user->{$attribute} !== $validated[$attribute]) {
                $eligibilityChanged = true;
                break;
            }
        }

        $user->update($validated);

        if ($eligibilityChanged) {
            \App\Jobs\RecomputeUserEligibilityJob::dispatch($user);
        }

        return response()->json([
            'success' => true,
            'data' => $user->fresh(),
            'message' => 'Profile updated successfully'
        ]);
    }
}
