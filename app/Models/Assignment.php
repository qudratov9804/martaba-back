<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id', 'lesson_id', 'title', 'description', 'instructions',
    'max_score', 'passing_score', 'deadline', 'allow_late_submission',
    'allowed_file_types_json', 'max_file_size', 'is_required',
])]
class Assignment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'allow_late_submission' => 'boolean',
            'allowed_file_types_json' => 'array',
            'is_required' => 'boolean',
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
     * @return BelongsTo<CourseLesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    /**
     * @return HasMany<AssignmentSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isPastDeadline(): bool
    {
        return $this->deadline !== null && now()->greaterThan($this->deadline);
    }
}
