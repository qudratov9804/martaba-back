<?php

namespace App\Http\Resources;

use App\Models\CourseProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CourseProgress */
class CourseProgressResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->course_id,
            'progress_percent' => $this->progress_percent,
            'completed_lessons' => $this->completed_lessons,
            'total_lessons' => $this->total_lessons,
            'required_lessons_completed' => $this->required_lessons_completed,
            'required_lessons_total' => $this->required_lessons_total,
            'last_lesson_id' => $this->last_lesson_id,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
