<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
    $this->admin = actingAsSuperAdmin($this->organization);
});

test('a super admin can view the organization analytics overview', function () {
    actingAsTeacher($this->organization);
    actingAsStudent($this->organization);
    Course::factory()->published()->create(['organization_id' => $this->organization->id]);

    $response = $this->withHeaders(bearerHeaderFor($this->admin))->getJson('/api/v1/admin/analytics/overview');

    $response->assertOk()
        ->assertJsonPath('data.total_teachers', 1)
        ->assertJsonPath('data.total_students', 1)
        ->assertJsonPath('data.total_courses', 1)
        ->assertJsonPath('data.published_courses', 1);
});

test('a teacher cannot access admin analytics', function () {
    $teacher = actingAsTeacher($this->organization);

    $this->withHeaders(bearerHeaderFor($teacher))
        ->getJson('/api/v1/admin/analytics/overview')
        ->assertStatus(403);
});

test('publishing a course records an audit log entry', function () {
    $teacher = actingAsTeacher($this->organization);
    $course = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $teacher->id,
    ]);
    CourseLesson::factory()->create([
        'course_id' => $course->id,
        'section_id' => CourseSection::factory()->create(['course_id' => $course->id])->id,
    ]);

    $this->withHeaders(bearerHeaderFor($teacher))
        ->postJson("/api/v1/teacher/courses/{$course->id}/publish")
        ->assertOk();

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'course.published',
        'subject_type' => Course::class,
        'subject_id' => $course->id,
        'user_id' => $teacher->id,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($this->admin))->getJson('/api/v1/admin/audit-logs');
    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});

test('a teacher cannot view audit logs', function () {
    $teacher = actingAsTeacher($this->organization);

    $this->withHeaders(bearerHeaderFor($teacher))
        ->getJson('/api/v1/admin/audit-logs')
        ->assertStatus(403);
});
