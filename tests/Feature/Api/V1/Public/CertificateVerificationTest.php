<?php

use App\Enums\EnrollmentSource;
use App\Enums\EnrollmentStatus;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');

    $this->organization = Organization::factory()->create();
    $this->course = Course::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'certificate_enabled' => true,
    ]);
    $section = CourseSection::factory()->create(['course_id' => $this->course->id]);
    $lesson = CourseLesson::factory()->create(['course_id' => $this->course->id, 'section_id' => $section->id, 'is_required' => true]);

    $this->student = actingAsStudent($this->organization);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $this->course->id,
        'student_id' => $this->student->id,
        'source' => EnrollmentSource::Free,
        'status' => EnrollmentStatus::Active,
    ]);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/lessons/{$lesson->id}/complete");

    $this->certificate = Certificate::where('student_id', $this->student->id)->firstOrFail();
});

test('a valid certificate verification code returns valid true with course and student info', function () {
    $response = $this->getJson("/api/v1/certificates/verify/{$this->certificate->verification_code}");

    $response->assertOk()
        ->assertJsonPath('data.valid', true)
        ->assertJsonPath('data.certificate_number', $this->certificate->certificate_number)
        ->assertJsonPath('data.student', $this->student->name)
        ->assertJsonPath('data.course', $this->course->title);
});

test('an unknown verification code returns valid false', function () {
    $this->getJson('/api/v1/certificates/verify/does-not-exist')
        ->assertOk()
        ->assertJsonPath('data.valid', false);
});

test('a revoked certificate fails verification', function () {
    $admin = actingAsSuperAdmin($this->organization);

    $this->withHeaders(bearerHeaderFor($admin))
        ->postJson("/api/v1/admin/certificates/{$this->certificate->id}/revoke")
        ->assertOk();

    $this->getJson("/api/v1/certificates/verify/{$this->certificate->verification_code}")
        ->assertOk()
        ->assertJsonPath('data.valid', false)
        ->assertJsonPath('data.status', 'revoked');
});
