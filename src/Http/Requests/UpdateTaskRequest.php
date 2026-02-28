<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskStatus;

/**
 * Update Task Request Validation
 */
class UpdateTaskRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'title' => ['string', 'min:3', 'max:255'],
            'description' => ['string'],
            'assigned_to' => ['integer'],
            'status_id' => ['integer', 'in:' . implode(',', TaskStatus::values())],
            'priority' => ['integer', 'between:1,5'],
            'due_date' => ['date'],
            'estimated_time' => ['integer', 'min:0'],
            'time_spent' => ['integer', 'min:0'],
            'billable_time' => ['integer', 'min:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.min' => 'Task title must be at least 3 characters',
            'title.max' => 'Task title must not exceed 255 characters',
            'priority.between' => 'Priority must be between 1 (lowest) and 5 (highest)',
            'estimated_time.min' => 'Estimated time cannot be negative',
            'time_spent.min' => 'Time spent cannot be negative',
            'billable_time.min' => 'Billable time cannot be negative',
        ];
    }
}
