<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Archived = 'archived';
}
