<?php

namespace App\Http\Requests\Teacher;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
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
            'type' => ['required', Rule::enum(QuestionType::class)],
            'question_text' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'points' => ['nullable', 'integer', 'min:1'],
            'difficulty' => ['nullable', Rule::enum(QuestionDifficulty::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'options' => ['nullable', 'array'],
            'options.*.text' => ['required_with:options', 'string'],
            'options.*.is_correct' => ['nullable', 'boolean'],
        ];
    }
}
