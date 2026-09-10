<?php

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Models\Course;

class UnpublishCourseAction
{
    public function handle(Course $course): Course
    {
        $course->update(['status' => CourseStatus::Unpublished]);

        return $course;
    }
}
