<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'lesson_id' => ['nullable', 'integer', 'exists:course_lessons,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'max_score' => ['nullable', 'integer', 'min:1'],
            'passing_score' => ['nullable', 'integer', 'min:0'],
            'deadline' => ['nullable', 'date'],
            'allow_late_submission' => ['nullable', 'boolean'],
            'allowed_file_types_json' => ['nullable', 'array'],
            'max_file_size' => ['nullable', 'integer', 'min:1'],
            'is_required' => ['nullable', 'boolean'],
        ];
    }
}
