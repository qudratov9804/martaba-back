<?php

namespace App\Models;

use App\Enums\QuizType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id', 'lesson_id', 'title', 'description', 'type',
    'passing_score', 'time_limit_minutes', 'max_attempts',
    'shuffle_questions', 'shuffle_options', 'show_correct_answers', 'is_required',
    'settings_json',
])]
class Quiz extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => QuizType::class,
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_correct_answers' => 'boolean',
            'is_required' => 'boolean',
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
     * @return BelongsTo<CourseLesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'lesson_id');
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<QuizAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function totalPoints(): int
    {
        return (int) $this->questions()->sum('points');
    }
}
