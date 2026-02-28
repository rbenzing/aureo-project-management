<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskStatus;

/**
 * Create Task Request Validation
 */
class CreateTaskRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['string'],
            'project_id' => ['required', 'integer'],
            'assigned_to' => ['integer'],
            'status_id' => ['integer', 'in:' . implode(',', TaskStatus::values())],
            'priority' => ['integer', 'between:1,5'],
            'due_date' => ['date'],
            'estimated_time' => ['integer', 'min:0'],
            'parent_task_id' => ['integer'],
            'is_subtask' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Task title is required',
            'title.min' => 'Task title must be at least 3 characters',
            'title.max' => 'Task title must not exceed 255 characters',
            'project_id.required' => 'Project is required',
            'priority.between' => 'Priority must be between 1 (lowest) and 5 (highest)',
            'estimated_time.min' => 'Estimated time cannot be negative',
        ];
    }
}
