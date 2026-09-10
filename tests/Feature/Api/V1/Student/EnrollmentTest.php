<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
});

test('a student can enroll in a free published course', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);

    $response = $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/courses/{$course->id}/enroll");

    $response->assertCreated()->assertJsonPath('data.status', 'active');
    $this->assertDatabaseHas('enrollments', [
        'course_id' => $course->id,
        'student_id' => $student->id,
        'source' => 'free',
    ]);
});

test('a student cannot enroll in a paid course through the free enroll endpoint', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->paid()->create(['organization_id' => $this->organization->id]);

    $response = $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/courses/{$course->id}/enroll");

    $response->assertStatus(422);
    $this->assertDatabaseMissing('enrollments', ['course_id' => $course->id, 'student_id' => $student->id]);
});

test('a student cannot enroll in an unpublished course', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->create(['organization_id' => $this->organization->id]);

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/courses/{$course->id}/enroll")
        ->assertStatus(422);
});

test('enrolling twice does not create duplicate enrollments', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);

    $this->withHeaders(bearerHeaderFor($student))->postJson("/api/v1/student/courses/{$course->id}/enroll");
    $this->withHeaders(bearerHeaderFor($student))->postJson("/api/v1/student/courses/{$course->id}/enroll");

    expect(Enrollment::where('course_id', $course->id)->where('student_id', $student->id)->count())->toBe(1);
});

test('a student can list only their own enrollments', function () {
    $student = actingAsStudent($this->organization);
    $otherStudent = actingAsStudent($this->organization);

    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $course->id,
        'student_id' => $student->id,
    ]);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $course->id,
        'student_id' => $otherStudent->id,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($student))->getJson('/api/v1/student/enrollments');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});

test('a student cannot view another students enrollment', function () {
    $student = actingAsStudent($this->organization);
    $otherStudent = actingAsStudent($this->organization);

    $enrollment = Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'student_id' => $otherStudent->id,
    ]);

    $this->withHeaders(bearerHeaderFor($student))
        ->getJson("/api/v1/student/enrollments/{$enrollment->id}")
        ->assertStatus(403);
});
