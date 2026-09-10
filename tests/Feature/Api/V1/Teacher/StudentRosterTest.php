<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
});

test('a teacher can view the enrolled students of their own course', function () {
    $teacher = actingAsTeacher($this->organization);
    $course = Course::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $teacher->id,
    ]);
    Enrollment::factory()->count(2)->create([
        'organization_id' => $this->organization->id,
        'course_id' => $course->id,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($teacher))
        ->getJson("/api/v1/teacher/courses/{$course->id}/students");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

test('a teacher cannot view the roster of another teachers course', function () {
    $teacherA = actingAsTeacher($this->organization);
    $teacherB = actingAsTeacher($this->organization);
    $course = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $teacherA->id,
    ]);

    $this->withHeaders(bearerHeaderFor($teacherB))
        ->getJson("/api/v1/teacher/courses/{$course->id}/students")
        ->assertStatus(403);
});
