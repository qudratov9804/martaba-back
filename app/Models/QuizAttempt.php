<?php

namespace App\Models;

use App\Enums\QuizAttemptStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'quiz_id', 'student_id', 'attempt_no', 'status',
    'score', 'max_score', 'percentage',
    'started_at', 'submitted_at', 'graded_at',
])]
class QuizAttempt extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => QuizAttemptStatus::class,
            'score' => 'float',
            'max_score' => 'float',
            'percentage' => 'float',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * @return HasMany<QuizAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
