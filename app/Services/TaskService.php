<?php

namespace App\Services;

use App\Contracts\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getPaginatedTasks(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->taskRepository->getPaginatedByUser($userId, $filters, $perPage);
    }

    public function getTaskById(int $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }

    public function createTask(array $data, ?UploadedFile $file = null): Task
    {
        // Handle file upload
        if ($file) {
            $data['file_path'] = $this->uploadFile($file);
        }

        return $this->taskRepository->create($data);
    }

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

    public function deleteTask(Task $task): bool
    {
        // Optionally delete associated file
        if ($task->file_path) {
            $this->deleteFile($task->file_path);
        }

        return $this->taskRepository->delete($task);
    }
    
    public function userOwnsTask(Task $task, int $userId): bool
    {
        return $this->taskRepository->belongsToUser($task, $userId);
    }

    protected function uploadFile(UploadedFile $file): string
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('tasks', $fileName, 'public');
    }

    protected function deleteFile(string $filePath): bool
    {
        return Storage::disk('public')->delete($filePath);
    }
}
