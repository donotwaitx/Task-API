<?php

namespace App\Contracts;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    /**
     * Get paginated tasks for a user with optional filters
     *
     * @param int $userId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedByUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Find a task by ID
     *
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task;

    /**
     * Create a new task
     *
     * @param array $data
     * @return Task
     */
    public function create(array $data): Task;

    /**
     * Update a task
     *
     * @param Task $task
     * @param array $data
     * @return Task
     */
    public function update(Task $task, array $data): Task;

    /**
     * Delete a task (soft delete)
     *
     * @param Task $task
     * @return bool
     */
    public function delete(Task $task): bool;

    /**
     * Check if task belongs to user
     *
     * @param Task $task
     * @param int $userId
     * @return bool
     */
    public function belongsToUser(Task $task, int $userId): bool;

    /**
     * Get all tasks for a user
     *
     * @param int $userId
     * @return Collection
     */
    public function getAllByUser(int $userId): Collection;
}
