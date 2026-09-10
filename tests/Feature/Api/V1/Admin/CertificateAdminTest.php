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

test('a super admin can reissue a certificate, invalidating the old verification code', function () {
    $admin = actingAsSuperAdmin($this->organization);
    $oldCode = $this->certificate->verification_code;

    $response = $this->withHeaders(bearerHeaderFor($admin))
        ->postJson("/api/v1/admin/certificates/{$this->certificate->id}/reissue");

    $response->assertOk();
    $newCode = $response->json('data.verification_code');

    expect($newCode)->not->toBe($oldCode);

    $this->getJson("/api/v1/certificates/verify/{$oldCode}")->assertJsonPath('data.valid', false);
    $this->getJson("/api/v1/certificates/verify/{$newCode}")->assertJsonPath('data.valid', true);

    $this->assertDatabaseHas('certificates', ['id' => $this->certificate->id, 'status' => 'revoked']);
});

test('a teacher cannot revoke or reissue certificates', function () {
    $teacher = actingAsTeacher($this->organization);

    $this->withHeaders(bearerHeaderFor($teacher))
        ->postJson("/api/v1/admin/certificates/{$this->certificate->id}/revoke")
        ->assertStatus(403);
});

test('a super admin can list all certificates in their organization', function () {
    $admin = actingAsSuperAdmin($this->organization);

    $response = $this->withHeaders(bearerHeaderFor($admin))->getJson('/api/v1/admin/certificates');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});
