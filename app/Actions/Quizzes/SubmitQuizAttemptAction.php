<?php

namespace App\Actions\Quizzes;

use App\Actions\Learning\EvaluateCourseCompletionAction;
use App\Enums\QuizAttemptStatus;
use App\Models\QuizAttempt;
use App\Services\Quizzes\QuizGradingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitQuizAttemptAction
{
    public function __construct(
        private readonly QuizGradingService $gradingService,
        private readonly EvaluateCourseCompletionAction $evaluateCourseCompletionAction,
    ) {}

    public function handle(QuizAttempt $attempt): QuizAttempt
    {
        if ($attempt->status !== QuizAttemptStatus::Started) {
            throw ValidationException::withMessages([
                'attempt' => ['This attempt has already been submitted.'],
            ]);
        }

        $attempt = DB::transaction(function () use ($attempt) {
            $questions = $attempt->quiz->questions()->with('options')->get()->keyBy('id');
            $answers = $attempt->answers()->get();

            $needsManualReview = false;
            $score = 0;

            foreach ($answers as $answer) {
                $question = $questions->get($answer->question_id);

                if (! $question) {
                    continue;
                }

                $graded = $this->gradingService->grade($answer, $question);
                $graded->save();

                if ($graded->points_awarded === null) {
                    $needsManualReview = true;
                } else {
                    $score += $graded->points_awarded;
                }
            }

            $maxScore = $attempt->max_score ?? $attempt->quiz->totalPoints();
            $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0.0;

            $attempt->update([
                'status' => $needsManualReview ? QuizAttemptStatus::Submitted : QuizAttemptStatus::Graded,
                'score' => $score,
                'max_score' => $maxScore,
                'percentage' => $percentage,
                'submitted_at' => now(),
                'graded_at' => $needsManualReview ? null : now(),
            ]);

            return $attempt->fresh(['answers']);
        });

        if ($attempt->status === QuizAttemptStatus::Graded) {
            $this->evaluateCourseCompletionAction->handle($attempt->student, $attempt->quiz->course);
        }

        return $attempt;
    }
}
