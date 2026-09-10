<?php

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Enums\CourseVisibility;
use App\Models\Course;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

class PublishCourseAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Course $course, ?User $actor = null): Course
    {
        if (! $course->lessons()->exists()) {
            throw ValidationException::withMessages([
                'course' => ['A course needs at least one lesson before it can be published.'],
            ]);
        }

        $previousStatus = $course->status;

        $course->update([
            'status' => CourseStatus::Published,
            'visibility' => $course->visibility === CourseVisibility::Private ? CourseVisibility::Public : $course->visibility,
            'published_at' => now(),
        ]);

        $this->auditLogger->record(
            $actor,
            'course.published',
            $course,
            ['status' => $previousStatus->value],
            ['status' => CourseStatus::Published->value],
        );

        return $course;
    }
}
