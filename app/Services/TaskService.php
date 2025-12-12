<?php

namespace App\Services;

use App\Contracts\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    /**
     * @var TaskRepositoryInterface
     */
    protected $taskRepository;

    /**
     * TaskService constructor.
     *
     * @param TaskRepositoryInterface $taskRepository
     */
    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Get paginated tasks for a user with filters
     *
     * @param int $userId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedTasks(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->taskRepository->getPaginatedByUser($userId, $filters, $perPage);
    }

    /**
     * Get a task by ID
     *
     * @param int $id
     * @return Task|null
     */
    public function getTaskById(int $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }

    /**
     * Create a new task
     *
     * @param array $data
     * @param UploadedFile|null $file
     * @return Task
     */
    public function createTask(array $data, ?UploadedFile $file = null): Task
    {
        // Handle file upload
        if ($file) {
            $data['file_path'] = $this->uploadFile($file);
        }

        return $this->taskRepository->create($data);
    }

    /**
     * Update a task
     *
     * @param Task $task
     * @param array $data
     * @param UploadedFile|null $file
     * @return Task
     */
    public function updateTask(Task $task, array $data, ?UploadedFile $file = null): Task
    {
        // Handle file upload
        if ($file) {
            // Delete old file if exists
            if ($task->file_path) {
                $this->deleteFile($task->file_path);
            }
            
            $data['file_path'] = $this->uploadFile($file);
        }

        return $this->taskRepository->update($task, $data);
    }

    /**
     * Delete a task
     *
     * @param Task $task
     * @return bool
     */
    public function deleteTask(Task $task): bool
    {
        // Optionally delete associated file
        if ($task->file_path) {
            $this->deleteFile($task->file_path);
        }

        return $this->taskRepository->delete($task);
    }

    /**
     * Check if task belongs to user
     *
     * @param Task $task
     * @param int $userId
     * @return bool
     */
    public function userOwnsTask(Task $task, int $userId): bool
    {
        return $this->taskRepository->belongsToUser($task, $userId);
    }

    /**
     * Upload a file
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function uploadFile(UploadedFile $file): string
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('tasks', $fileName, 'public');
    }

    /**
     * Delete a file
     *
     * @param string $filePath
     * @return bool
     */
    protected function deleteFile(string $filePath): bool
    {
        return Storage::disk('public')->delete($filePath);
    }
}
