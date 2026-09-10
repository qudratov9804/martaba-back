<?php

namespace App\Models;

use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lesson_id', 'content_type', 'text_content', 'media_id',
    'external_url', 'embed_code', 'metadata_json', 'sort_order',
])]
class CourseContent extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'content_type' => ContentType::class,
            'metadata_json' => 'array',
        ];
    }

    /**
     * @return BelongsTo<CourseLesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
