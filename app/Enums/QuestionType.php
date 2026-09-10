<?php

namespace App\Enums;

enum QuestionType: string
{
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case ShortAnswer = 'short_answer';
    case LongAnswer = 'long_answer';
    case Numeric = 'numeric';
    case Matching = 'matching';
    case Ordering = 'ordering';

    /**
     * Question types that can be scored automatically without human review.
     */
    public function isAutoGradable(): bool
    {
        return match ($this) {
            self::LongAnswer, self::Matching => false,
            default => true,
        };
    }
}
