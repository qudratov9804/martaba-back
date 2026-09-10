<?php

namespace Database\Factories;

use App\Enums\EnrollmentSource;
use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'course_id' => Course::factory(),
            'student_id' => User::factory(),
            'source' => EnrollmentSource::Free,
            'status' => EnrollmentStatus::Active,
            'enrolled_at' => now(),
        ];
    }
}
