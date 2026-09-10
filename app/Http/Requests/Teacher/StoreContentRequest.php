<?php

namespace App\Http\Requests\Teacher;

use App\Enums\ContentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentRequest extends FormRequest
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
            'content_type' => ['required', Rule::enum(ContentType::class)],
            'text_content' => ['nullable', 'string'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'embed_code' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
