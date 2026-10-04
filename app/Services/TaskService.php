<?php

namespace App\Services;

use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Exception;

class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {}

    public function getAllTasks(array $filters, int $perPage = 12)
    {
        return $this->taskRepository->getAllPaginated($filters, $perPage);
    }

    public function getTask(int $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }

    public function createTask(array $data, array $rulesData, int $userId): Task
    {
        DB::beginTransaction();

        try {
            $data['created_by'] = $userId;
            $task = $this->taskRepository->create($data);

            if (!empty($rulesData)) {
                $task->rules()->createMany($rulesData);
            }

            DB::commit();
            
            // Dispatch job to assign users based on rules
            \App\Jobs\AssignTaskJob::dispatch($task);

            return $task;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateTask(int $id, array $data, ?array $rulesData): bool
    {
        DB::beginTransaction();

        try {
            $updated = $this->taskRepository->update($id, $data);
            $task = $this->getTask($id);
            
            if ($rulesData !== null && $task) {
                $task->rules()->delete(); // Clear existing rules
                if (!empty($rulesData)) {
                    $task->rules()->createMany($rulesData);
                }
            }

            DB::commit();

            if ($rulesData !== null && $task) {
                \App\Jobs\RecomputeTaskEligibilityJob::dispatch($task->fresh('rules'));
            }

            return $updated;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteTask(int $id): bool
    {
        return $this->taskRepository->delete($id);
    }
}
