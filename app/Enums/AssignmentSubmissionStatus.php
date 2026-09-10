<?php

namespace App\Enums;

enum AssignmentSubmissionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Late = 'late';
    case Graded = 'graded';
    case Returned = 'returned';
}
