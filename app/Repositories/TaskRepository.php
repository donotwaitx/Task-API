<?php

namespace App\Repositories;

use App\Contracts\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    protected $model;

    public function __construct(Task $model)
    {
        $this->model = $model;
    }

    public function getPaginatedByUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with('user:id,name,email')
            ->where('user_id', $userId);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['due_date'])) {
            $query->whereDate('due_date', $filters['due_date']);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Task
    {
        return $this->model->with('user:id,name,email')->find($id);
    }

    public function create(array $data): Task
    {
        $task = $this->model->create($data);
        $task->load('user:id,name,email');
        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        $task->load('user:id,name,email');
        return $task;
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function belongsToUser(Task $task, int $userId): bool
    {
        return $task->user_id === $userId;
    }

    public function getAllByUser(int $userId): Collection
    {
        return $this->model->with('user:id,name,email')
            ->where('user_id', $userId)
            ->get();
    }
}
