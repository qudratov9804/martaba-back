<?php

namespace App\Enums;

enum QuizType: string
{
    case Practice = 'practice';
    case Lesson = 'lesson';
    case Section = 'section';
    case Final = 'final';
}
