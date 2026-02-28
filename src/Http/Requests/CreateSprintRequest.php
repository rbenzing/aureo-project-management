<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SprintStatus;

/**
 * Create Sprint Request Validation
 */
class CreateSprintRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['string'],
            'project_id' => ['required', 'integer'],
            'status_id' => ['integer', 'in:' . implode(',', SprintStatus::values())],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'goal' => ['string'],
            'capacity_hours' => ['integer', 'min:0'],
            'capacity_story_points' => ['integer', 'min:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Sprint name is required',
            'name.min' => 'Sprint name must be at least 3 characters',
            'name.max' => 'Sprint name must not exceed 255 characters',
            'project_id.required' => 'Project is required',
            'start_date.required' => 'Start date is required',
            'end_date.required' => 'End date is required',
            'capacity_hours.min' => 'Capacity hours cannot be negative',
            'capacity_story_points.min' => 'Capacity story points cannot be negative',
        ];
    }
}
