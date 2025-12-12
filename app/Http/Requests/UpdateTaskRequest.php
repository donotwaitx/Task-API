<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,done',
            'due_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required',
            'title.max' => 'Task title cannot exceed 255 characters',
            'status.in' => 'Status must be one of: pending, in_progress, done',
            'due_date.date' => 'Due date must be a valid date',
            'file.mimes' => 'File must be a PDF, DOC, DOCX, JPG, JPEG, or PNG',
            'file.max' => 'File size cannot exceed 2MB',
        ];
    }
}
