<?php

namespace App\Contracts;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{       
    public function getPaginatedByUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function findById(int $id): ?Task;

    public function create(array $data): Task;

    public function update(Task $task, array $data): Task;

    public function delete(Task $task): bool;

    public function belongsToUser(Task $task, int $userId): bool;

    public function getAllByUser(int $userId): Collection;
}
