<?php

namespace App\Actions\Courses;

use App\Enums\CourseInstructorRole;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCourseAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $teacher, array $data): Course
    {
        return DB::transaction(function () use ($teacher, $data) {
            $course = Course::create([
                ...$data,
                'organization_id' => $teacher->organization_id,
                'created_by' => $teacher->id,
            ]);

            $course->courseInstructors()->create([
                'user_id' => $teacher->id,
                'role' => CourseInstructorRole::Instructor,
                'is_primary' => true,
                'created_at' => now(),
            ]);

            return $course;
        });
    }
}
