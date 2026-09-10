<?php

use App\Enums\EnrollmentSource;
use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
    $this->course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);
    $this->section = CourseSection::factory()->create(['course_id' => $this->course->id, 'is_published' => true]);
    $this->lessonOne = CourseLesson::factory()->create([
        'course_id' => $this->course->id,
        'section_id' => $this->section->id,
        'is_published' => true,
        'is_required' => true,
        'sort_order' => 0,
    ]);
    $this->lessonTwo = CourseLesson::factory()->create([
        'course_id' => $this->course->id,
        'section_id' => $this->section->id,
        'is_published' => true,
        'is_required' => true,
        'sort_order' => 1,
    ]);
});

function enrollStudent(Organization $organization, Course $course): User
{
    $student = actingAsStudent($organization);
    Enrollment::factory()->create([
        'organization_id' => $organization->id,
        'course_id' => $course->id,
        'student_id' => $student->id,
        'source' => EnrollmentSource::Free,
        'status' => EnrollmentStatus::Active,
    ]);

    return $student;
}

test('a non-enrolled student cannot access a non-preview lesson', function () {
    $student = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($student))
        ->getJson("/api/v1/student/lessons/{$this->lessonOne->id}")
        ->assertStatus(403);
});

test('anyone enrolled can view a preview lesson without enrollment', function () {
    $student = actingAsStudent($this->organization);
    $this->lessonOne->update(['is_preview' => true]);

    $this->withHeaders(bearerHeaderFor($student))
        ->getJson("/api/v1/student/lessons/{$this->lessonOne->id}")
        ->assertOk();
});

test('an enrolled student can view the course learn overview', function () {
    $student = enrollStudent($this->organization, $this->course);

    $response = $this->withHeaders(bearerHeaderFor($student))
        ->getJson("/api/v1/student/courses/{$this->course->id}/learn");

    $response->assertOk()->assertJsonPath('data.course.slug', $this->course->slug);
});

test('an enrolled student can start and complete a lesson', function () {
    $student = enrollStudent($this->organization, $this->course);

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/lessons/{$this->lessonOne->id}/start")
        ->assertOk()
        ->assertJsonPath('data.status', 'in_progress');

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/lessons/{$this->lessonOne->id}/complete")
        ->assertOk()
        ->assertJsonPath('data.status', 'completed');

    $this->assertDatabaseHas('lesson_progress', [
        'student_id' => $student->id,
        'lesson_id' => $this->lessonOne->id,
        'status' => 'completed',
    ]);
});

test('completing all required lessons marks the course and enrollment as completed', function () {
    $student = enrollStudent($this->organization, $this->course);

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/lessons/{$this->lessonOne->id}/complete")
        ->assertOk();

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/lessons/{$this->lessonTwo->id}/complete")
        ->assertOk();

    $this->assertDatabaseHas('course_progress', [
        'student_id' => $student->id,
        'course_id' => $this->course->id,
        'progress_percent' => 100,
    ]);

    $this->assertDatabaseHas('enrollments', [
        'student_id' => $student->id,
        'course_id' => $this->course->id,
        'status' => 'completed',
    ]);
});

test('a student can update lesson watch progress without completing it', function () {
    $student = enrollStudent($this->organization, $this->course);

    $response = $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/lessons/{$this->lessonOne->id}/progress", [
            'watch_seconds' => 120,
            'last_position_seconds' => 90,
            'progress_percent' => 40,
        ]);

    $response->assertOk()->assertJsonPath('data.status', 'in_progress');
    $this->assertDatabaseHas('lesson_progress', [
        'student_id' => $student->id,
        'lesson_id' => $this->lessonOne->id,
        'watch_seconds' => 120,
        'progress_percent' => 40,
    ]);
});
