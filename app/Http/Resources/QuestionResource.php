<?php

namespace App\Http\Resources;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Question */
class QuestionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'type' => $this->type,
            'question_text' => $this->question_text,
            'explanation' => $this->explanation,
            'points' => $this->points,
            'difficulty' => $this->difficulty,
            'sort_order' => $this->sort_order,
            'options' => $this->whenLoaded('options', fn () => $this->options->map(fn ($option) => [
                'id' => $option->id,
                'text' => $option->text,
                'is_correct' => $option->is_correct,
            ])),
        ];
    }
}
