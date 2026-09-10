<?php

namespace App\Actions\Quizzes;

use App\Enums\QuizAttemptStatus;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Validation\ValidationException;

class AnswerQuizQuestionAction
{
    /**
     * @param  array{question_id: int, selected_option_ids?: list<int>, text_answer?: string}  $data
     */
    public function handle(QuizAttempt $attempt, array $data): QuizAnswer
    {
        if ($attempt->status !== QuizAttemptStatus::Started) {
            throw ValidationException::withMessages([
                'attempt' => ['This attempt has already been submitted.'],
            ]);
        }

        return QuizAnswer::updateOrCreate(
            ['quiz_attempt_id' => $attempt->id, 'question_id' => $data['question_id']],
            [
                'selected_option_ids' => $data['selected_option_ids'] ?? null,
                'text_answer' => $data['text_answer'] ?? null,
            ],
        );
    }
}
