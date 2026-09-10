<?php

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Models\Course;

class ArchiveCourseAction
{
    public function handle(Course $course): Course
    {
        $course->update(['status' => CourseStatus::Archived]);

        return $course;
    }
}
