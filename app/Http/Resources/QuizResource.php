<?php

namespace App\Http\Resources;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Quiz */
class QuizResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'lesson_id' => $this->lesson_id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'passing_score' => $this->passing_score,
            'time_limit_minutes' => $this->time_limit_minutes,
            'max_attempts' => $this->max_attempts,
            'shuffle_questions' => $this->shuffle_questions,
            'shuffle_options' => $this->shuffle_options,
            'show_correct_answers' => $this->show_correct_answers,
            'is_required' => $this->is_required,
            'total_points' => $this->whenLoaded('questions', fn () => $this->questions->sum('points')),
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
