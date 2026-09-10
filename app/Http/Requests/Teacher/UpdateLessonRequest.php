<?php

namespace App\Http\Requests\Teacher;

use App\Enums\LessonType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash'],
            'description' => ['nullable', 'string'],
            'lesson_type' => ['sometimes', 'required', Rule::enum(LessonType::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
