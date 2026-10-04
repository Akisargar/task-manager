<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function getAllPaginated(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = Task::with([
            'rules',
            'assignedUsers:id,name,email,role,department,location,active_task_count',
            'creator:id,name,email'
        ]);

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority']) && $filters['priority'] !== '') {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['assignment_status']) && $filters['assignment_status'] !== '') {
            if ($filters['assignment_status'] === 'unassigned') {
                $query->doesntHave('assignments');
            } elseif ($filters['assignment_status'] === 'assigned') {
                $query->has('assignments');
            }
        }

        if (isset($filters['sort_by'])) {
            $order = $filters['order'] ?? 'asc';
            if ($filters['sort_by'] === 'priority') {
                $query->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END {$order}")
                      ->orderBy('id', 'asc');
            } else {
                $query->orderBy($filters['sort_by'], $order);
            }
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Task
    {
        return Task::with([
            'rules',
            'assignedUsers:id,name,email,role,department,location,active_task_count',
            'creator:id,name,email'
        ])->find($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return Task::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return (bool) Task::destroy($id);
    }
}
