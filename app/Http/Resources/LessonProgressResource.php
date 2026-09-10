<?php

namespace App\Http\Resources;

use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LessonProgress */
class LessonProgressResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'course_id' => $this->course_id,
            'status' => $this->status,
            'progress_percent' => $this->progress_percent,
            'watch_seconds' => $this->watch_seconds,
            'last_position_seconds' => $this->last_position_seconds,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
