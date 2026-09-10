<?php

namespace App\Actions\Quizzes;

use App\Actions\Learning\EvaluateCourseCompletionAction;
use App\Enums\QuizAttemptStatus;
use App\Models\QuizAnswer;

class GradeQuizAnswerAction
{
    public function __construct(private readonly EvaluateCourseCompletionAction $evaluateCourseCompletionAction) {}

    public function handle(QuizAnswer $answer, float $pointsAwarded): QuizAnswer
    {
        $answer->update([
            'is_correct' => $pointsAwarded >= $answer->question->points,
            'points_awarded' => $pointsAwarded,
        ]);

        $attempt = $answer->attempt;
        $stillPending = $attempt->answers()->whereNull('points_awarded')->exists();

        if (! $stillPending) {
            $score = (float) $attempt->answers()->sum('points_awarded');
            $percentage = $attempt->max_score > 0 ? round(($score / $attempt->max_score) * 100, 2) : 0.0;

            $attempt->update([
                'status' => QuizAttemptStatus::Graded,
                'score' => $score,
                'percentage' => $percentage,
                'graded_at' => now(),
            ]);

            $this->evaluateCourseCompletionAction->handle($attempt->student, $attempt->quiz->course);
        }

        return $answer->fresh();
    }
}
