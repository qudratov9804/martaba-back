<?php

namespace App\Enums;

enum CourseVisibility: string
{
    case Public = 'public';
    case Private = 'private';
    case Unlisted = 'unlisted';
}
