<?php

namespace App\Enums;

enum CourseInstructorRole: string
{
    case Instructor = 'instructor';
    case Assistant = 'assistant';
    case Reviewer = 'reviewer';
}
