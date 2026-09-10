<?php

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
});

test('a teacher can create a course', function () {
    $teacher = actingAsTeacher($this->organization);

    $response = $this->withHeaders(bearerHeaderFor($teacher))->postJson('/api/v1/teacher/courses', [
        'title' => 'Laravel From Scratch',
        'slug' => 'laravel-from-scratch',
        'pricing_type' => 'free',
    ]);

    $response->assertCreated()->assertJsonPath('data.slug', 'laravel-from-scratch');
    $this->assertDatabaseHas('courses', ['slug' => 'laravel-from-scratch', 'created_by' => $teacher->id]);
    $this->assertDatabaseHas('course_instructors', ['user_id' => $teacher->id, 'is_primary' => true]);
});

test('paid courses require a price', function () {
    $teacher = actingAsTeacher($this->organization);

    $response = $this->withHeaders(bearerHeaderFor($teacher))->postJson('/api/v1/teacher/courses', [
        'title' => 'Paid Course',
        'slug' => 'paid-course',
        'pricing_type' => 'paid',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['price_minor']);
});

test('a teacher cannot see or edit another teachers course', function () {
    $teacherA = actingAsTeacher($this->organization);
    $teacherB = actingAsTeacher($this->organization);

    $course = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $teacherA->id,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($teacherB))->getJson("/api/v1/teacher/courses/{$course->id}");
    $response->assertStatus(403);

    $updateResponse = $this->withHeaders(bearerHeaderFor($teacherB))
        ->putJson("/api/v1/teacher/courses/{$course->id}", ['title' => 'Hijacked']);
    $updateResponse->assertStatus(403);

    $this->assertDatabaseHas('courses', ['id' => $course->id, 'title' => $course->title]);
});

test('a course cannot be published without any lessons', function () {
    $teacher = actingAsTeacher($this->organization);
    $course = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $teacher->id,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($teacher))->postJson("/api/v1/teacher/courses/{$course->id}/publish");

    $response->assertStatus(422);
    expect($course->fresh()->status)->toBe(CourseStatus::Draft);
});

test('a course with a lesson can be published', function () {
    $teacher = actingAsTeacher($this->organization);
    $course = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $teacher->id,
    ]);
    $section = CourseSection::factory()->create(['course_id' => $course->id]);
    CourseLesson::factory()->create(['course_id' => $course->id, 'section_id' => $section->id]);

    $response = $this->withHeaders(bearerHeaderFor($teacher))->postJson("/api/v1/teacher/courses/{$course->id}/publish");

    $response->assertOk()->assertJsonPath('data.status', 'published');
});

test('a super admin can view any course', function () {
    $teacher = actingAsTeacher($this->organization);
    $admin = actingAsSuperAdmin($this->organization);
    $course = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $teacher->id,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($admin))->getJson("/api/v1/teacher/courses/{$course->id}");

    $response->assertOk();
});

test('a student cannot create a course', function () {
    $student = actingAsStudent($this->organization);

    $response = $this->withHeaders(bearerHeaderFor($student))->postJson('/api/v1/teacher/courses', [
        'title' => 'Blocked',
        'slug' => 'blocked',
        'pricing_type' => 'free',
    ]);

    $response->assertStatus(403);
});
