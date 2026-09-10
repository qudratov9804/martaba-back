<?php

namespace App\Actions\Enrollments;

use App\Enums\CoursePricingType;
use App\Enums\CourseStatus;
use App\Enums\EnrollmentSource;
use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class EnrollStudentAction
{
    public function handle(User $student, Course $course, EnrollmentSource $source = EnrollmentSource::Free): Enrollment
    {
        if ($course->status !== CourseStatus::Published) {
            throw ValidationException::withMessages([
                'course' => ['This course is not available for enrollment.'],
            ]);
        }

        if ($source === EnrollmentSource::Free && $course->pricing_type !== CoursePricingType::Free) {
            throw ValidationException::withMessages([
                'course' => ['This course requires payment. Use the checkout flow to enroll.'],
            ]);
        }

        $existing = Enrollment::query()
            ->where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return Enrollment::create([
            'organization_id' => $course->organization_id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'source' => $source,
            'status' => EnrollmentStatus::Active,
            'enrolled_at' => now(),
        ]);
    }
}
