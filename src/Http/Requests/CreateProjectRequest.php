<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ProjectStatus;

/**
 * Create Project Request Validation
 */
class CreateProjectRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['string'],
            'key_code' => ['string', 'min:2', 'max:10'],
            'owner_id' => ['required', 'integer'],
            'company_id' => ['integer'],
            'status_id' => ['integer', 'in:' . implode(',', ProjectStatus::values())],
            'start_date' => ['date'],
            'due_date' => ['date'],
            'budget' => ['numeric', 'min:0'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Project name is required',
            'name.min' => 'Project name must be at least 3 characters',
            'name.max' => 'Project name must not exceed 255 characters',
            'key_code.min' => 'Key code must be at least 2 characters',
            'key_code.max' => 'Key code must not exceed 10 characters',
            'owner_id.required' => 'Project owner is required',
            'budget.min' => 'Budget cannot be negative',
        ];
    }
}
