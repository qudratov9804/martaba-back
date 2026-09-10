<?php

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Enums\CourseVisibility;
use App\Models\Course;
use Illuminate\Validation\ValidationException;

class PublishCourseAction
{
    public function handle(Course $course): Course
    {
        if (! $course->lessons()->exists()) {
            throw ValidationException::withMessages([
                'course' => ['A course needs at least one lesson before it can be published.'],
            ]);
        }

        $course->update([
            'status' => CourseStatus::Published,
            'visibility' => $course->visibility === CourseVisibility::Private ? CourseVisibility::Public : $course->visibility,
            'published_at' => now(),
        ]);

        return $course;
    }
}
