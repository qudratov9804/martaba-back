<?php

namespace App\Services\Courses;

use App\Enums\ReviewStatus;
use App\Models\Course;

class CourseRatingService
{
    public function recalculate(Course $course): void
    {
        $published = $course->reviews()->where('status', ReviewStatus::Published);

        $course->update([
            'rating_average' => round((float) $published->avg('rating'), 2),
            'rating_count' => $published->count(),
        ]);
    }
}
