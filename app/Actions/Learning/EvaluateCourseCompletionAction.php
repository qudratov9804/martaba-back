<?php

namespace App\Actions\Learning;

use App\Enums\EnrollmentStatus;
use App\Events\CourseCompleted;
use App\Models\Course;
use App\Models\User;
use App\Services\Learning\CourseCompletionService;

class EvaluateCourseCompletionAction
{
    public function __construct(private readonly CourseCompletionService $completionService) {}

    public function handle(User $student, Course $course): void
    {
        $enrollment = $course->enrollments()
            ->where('student_id', $student->id)
            ->where('status', EnrollmentStatus::Active)
            ->first();

        if (! $enrollment) {
            return;
        }

        if (! $this->completionService->isComplete($student, $course)) {
            return;
        }

        $enrollment->update(['status' => EnrollmentStatus::Completed, 'completed_at' => now()]);

        CourseCompleted::dispatch($enrollment);
    }
}
