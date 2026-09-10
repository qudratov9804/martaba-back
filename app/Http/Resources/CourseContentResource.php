<?php

namespace App\Http\Resources;

use App\Models\CourseContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CourseContent */
class CourseContentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'content_type' => $this->content_type,
            'text_content' => $this->text_content,
            'media_id' => $this->media_id,
            'media' => new MediaResource($this->whenLoaded('media')),
            'external_url' => $this->external_url,
            'embed_code' => $this->embed_code,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
