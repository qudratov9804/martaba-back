<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
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
            'text_submission' => ['nullable', 'string', 'required_without:media_id'],
            'media_id' => ['nullable', 'integer', 'exists:media,id', 'required_without:text_submission'],
        ];
    }
}
