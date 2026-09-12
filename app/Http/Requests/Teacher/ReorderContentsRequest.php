<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class ReorderContentsRequest extends FormRequest
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
            'content_ids' => ['required', 'array', 'min:1'],
            'content_ids.*' => ['integer', 'distinct'],
        ];
    }
}
