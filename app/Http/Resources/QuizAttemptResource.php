<?php

namespace App\Http\Resources;

use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuizAttempt */
class QuizAttemptResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'student_id' => $this->student_id,
            'attempt_no' => $this->attempt_no,
            'status' => $this->status,
            'score' => $this->score,
            'max_score' => $this->max_score,
            'percentage' => $this->percentage,
            'started_at' => $this->started_at?->toIso8601String(),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'graded_at' => $this->graded_at?->toIso8601String(),
            'answers' => QuizAnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}
