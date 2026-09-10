<?php

namespace App\Models;

use App\Enums\LessonType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id', 'section_id', 'title', 'slug', 'description', 'lesson_type',
    'sort_order', 'is_preview', 'is_required', 'is_published',
    'estimated_duration_minutes', 'settings_json',
])]
class CourseLesson extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'lesson_type' => LessonType::class,
            'is_preview' => 'boolean',
            'is_required' => 'boolean',
            'is_published' => 'boolean',
            'settings_json' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<CourseSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    /**
     * @return HasMany<CourseContent, $this>
     */
    public function contents(): HasMany
    {
        return $this->hasMany(CourseContent::class, 'lesson_id')->orderBy('sort_order');
    }
}
