<?php

namespace App\Actions\Courses;

use App\Models\Course;

class UpdateCourseAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Course $course, array $data): Course
    {
        $course->update($data);

        return $course;
    }
}
