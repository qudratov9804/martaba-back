<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonProgressRequest extends FormRequest
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
            'watch_seconds' => ['nullable', 'integer', 'min:0'],
            'last_position_seconds' => ['nullable', 'integer', 'min:0'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
