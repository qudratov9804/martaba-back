<?php

namespace App\Http\Resources;

use App\Models\CourseLesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CourseLesson */
class CourseLessonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'section_id' => $this->section_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'lesson_type' => $this->lesson_type,
            'sort_order' => $this->sort_order,
            'is_preview' => $this->is_preview,
            'is_required' => $this->is_required,
            'is_published' => $this->is_published,
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'contents' => CourseContentResource::collection($this->whenLoaded('contents')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
