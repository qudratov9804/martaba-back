<?php

namespace App\Http\Resources;

use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuizAnswer */
class QuizAnswerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'selected_option_ids' => $this->selected_option_ids,
            'text_answer' => $this->text_answer,
            'is_correct' => $this->is_correct,
            'points_awarded' => $this->points_awarded,
        ];
    }
}
