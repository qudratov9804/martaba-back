<?php

use App\Enums\EnrollmentSource;
use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
    $this->teacher = actingAsTeacher($this->organization);
    $this->course = Course::factory()->published()->paid(10000)->create([
        'organization_id' => $this->organization->id,
        'created_by' => $this->teacher->id,
    ]);
});

test('a teacher can view their analytics overview', function () {
    Enrollment::factory()->count(3)->create([
        'organization_id' => $this->organization->id,
        'course_id' => $this->course->id,
        'source' => EnrollmentSource::Free,
        'status' => EnrollmentStatus::Active,
    ]);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $this->course->id,
        'source' => EnrollmentSource::Free,
        'status' => EnrollmentStatus::Completed,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($this->teacher))->getJson('/api/v1/teacher/analytics/overview');

    $response->assertOk()
        ->assertJsonPath('data.total_courses', 1)
        ->assertJsonPath('data.total_enrollments', 4)
        ->assertJsonPath('data.completion_rate', 25);
});

test('a teacher only sees revenue from their own courses', function () {
    $otherTeacher = actingAsTeacher($this->organization);
    $otherCourse = Course::factory()->published()->paid(20000)->create([
        'organization_id' => $this->organization->id,
        'created_by' => $otherTeacher->id,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($this->teacher))->getJson('/api/v1/teacher/analytics/revenue');

    $response->assertOk();
    $courseIds = collect($response->json('data'))->pluck('course_id');
    expect($courseIds)->toContain($this->course->id);
    expect($courseIds)->not->toContain($otherCourse->id);
});

test('a teacher can view analytics for their own course', function () {
    $response = $this->withHeaders(bearerHeaderFor($this->teacher))
        ->getJson("/api/v1/teacher/analytics/courses/{$this->course->id}");

    $response->assertOk()->assertJsonPath('data.course_id', $this->course->id);
});

test('a teacher cannot view analytics for another teachers course', function () {
    $otherTeacher = actingAsTeacher($this->organization);
    $otherCourse = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $otherTeacher->id,
    ]);

    $this->withHeaders(bearerHeaderFor($this->teacher))
        ->getJson("/api/v1/teacher/analytics/courses/{$otherCourse->id}")
        ->assertStatus(403);
});

test('a student cannot access teacher analytics', function () {
    $student = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($student))
        ->getJson('/api/v1/teacher/analytics/overview')
        ->assertStatus(403);
});
