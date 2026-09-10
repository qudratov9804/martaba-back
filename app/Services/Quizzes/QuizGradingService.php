<?php

namespace App\Services\Quizzes;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuizAnswer;

class QuizGradingService
{
    /**
     * Grade a single answer against its question. Returns the answer with
     * is_correct/points_awarded populated, or left null when the question
     * type requires manual review.
     */
    public function grade(QuizAnswer $answer, Question $question): QuizAnswer
    {
        if (! $question->type->isAutoGradable()) {
            $answer->is_correct = null;
            $answer->points_awarded = null;

            return $answer;
        }

        $isCorrect = match ($question->type) {
            QuestionType::SingleChoice, QuestionType::TrueFalse => $this->gradeSingleChoice($answer, $question),
            QuestionType::MultipleChoice => $this->gradeMultipleChoice($answer, $question),
            QuestionType::ShortAnswer => $this->gradeShortAnswer($answer, $question),
            QuestionType::Numeric => $this->gradeNumeric($answer, $question),
            QuestionType::Ordering => $this->gradeOrdering($answer, $question),
            default => false,
        };

        $answer->is_correct = $isCorrect;
        $answer->points_awarded = $isCorrect ? $question->points : 0;

        return $answer;
    }

    private function gradeSingleChoice(QuizAnswer $answer, Question $question): bool
    {
        $correctOptionId = $question->options->firstWhere('is_correct', true)?->id;
        $selected = $answer->selected_option_ids ?? [];

        return count($selected) === 1 && (int) $selected[0] === $correctOptionId;
    }

    private function gradeMultipleChoice(QuizAnswer $answer, Question $question): bool
    {
        $correctIds = $question->options->where('is_correct', true)->pluck('id')->sort()->values()->all();
        $selected = collect($answer->selected_option_ids ?? [])->map(fn ($id) => (int) $id)->sort()->values()->all();

        return $correctIds === $selected;
    }

    private function gradeShortAnswer(QuizAnswer $answer, Question $question): bool
    {
        $correctAnswers = $question->options->pluck('text')
            ->map(fn ($text) => mb_strtolower(trim($text)));

        return $correctAnswers->contains(mb_strtolower(trim((string) $answer->text_answer)));
    }

    private function gradeNumeric(QuizAnswer $answer, Question $question): bool
    {
        $correctValue = $question->options->first()?->text;

        if ($correctValue === null || $answer->text_answer === null) {
            return false;
        }

        return abs((float) $correctValue - (float) $answer->text_answer) < 0.0001;
    }

    private function gradeOrdering(QuizAnswer $answer, Question $question): bool
    {
        $correctOrder = $question->options->pluck('id')->all();
        $selected = collect($answer->selected_option_ids ?? [])->map(fn ($id) => (int) $id)->all();

        return $correctOrder === $selected;
    }
}
