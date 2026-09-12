<?php

namespace App\Http\Resources;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Course */
class CourseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'category_id' => $this->category_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'thumbnail_path' => $this->thumbnail_path,
            'cover_path' => $this->cover_path,
            'level' => $this->level,
            'language' => $this->language,
            'pricing_type' => $this->pricing_type,
            'price_minor' => $this->price_minor,
            'discount_price_minor' => $this->discount_price_minor,
            'currency' => $this->currency,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'featured' => $this->featured,
            'certificate_enabled' => $this->certificate_enabled,
            'reviews_enabled' => $this->reviews_enabled,
            'rating_average' => $this->rating_average,
            'rating_count' => $this->rating_count,
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'published_at' => $this->published_at?->toIso8601String(),
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'completion_rules_json' => $this->completion_rules_json,
            'sections' => CourseSectionResource::collection($this->whenLoaded('sections')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
