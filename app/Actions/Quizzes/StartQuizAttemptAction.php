<?php

namespace App\Actions\Quizzes;

use App\Enums\QuizAttemptStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class StartQuizAttemptAction
{
    public function handle(User $student, Quiz $quiz): QuizAttempt
    {
        $attemptCount = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->count();

        if ($quiz->max_attempts !== null && $attemptCount >= $quiz->max_attempts) {
            throw ValidationException::withMessages([
                'quiz' => ['You have used all of your allowed attempts for this quiz.'],
            ]);
        }

        return QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'attempt_no' => $attemptCount + 1,
            'status' => QuizAttemptStatus::Started,
            'max_score' => $quiz->totalPoints(),
            'started_at' => now(),
        ]);
    }
}
