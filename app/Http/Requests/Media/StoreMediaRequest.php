<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
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
            'file' => [
                'required', 'file', 'max:512000',
                'mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,webm,mp3,wav,pdf,doc,docx,ppt,pptx,zip',
            ],
            'visibility' => ['nullable', 'in:public,private'],
        ];
    }
}
