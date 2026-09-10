<?php

namespace App\Http\Resources;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Assignment */
class AssignmentResource extends JsonResource
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
            'instructions' => $this->instructions,
            'max_score' => $this->max_score,
            'passing_score' => $this->passing_score,
            'deadline' => $this->deadline?->toIso8601String(),
            'allow_late_submission' => $this->allow_late_submission,
            'is_required' => $this->is_required,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
