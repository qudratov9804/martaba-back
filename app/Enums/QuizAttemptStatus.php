<?php

namespace App\Enums;

enum QuizAttemptStatus: string
{
    case Started = 'started';
    case Submitted = 'submitted';
    case Graded = 'graded';
    case Expired = 'expired';
}
