<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
});

test('a super admin can list and publish courses in their organization', function () {
    $admin = actingAsSuperAdmin($this->organization);
    $course = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'status' => 'review',
    ]);
    $section = CourseSection::factory()->create(['course_id' => $course->id]);
    CourseLesson::factory()->create(['course_id' => $course->id, 'section_id' => $section->id]);

    $this->withHeaders(bearerHeaderFor($admin))
        ->getJson('/api/v1/admin/courses')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->withHeaders(bearerHeaderFor($admin))
        ->postJson("/api/v1/admin/courses/{$course->id}/publish")
        ->assertOk()
        ->assertJsonPath('data.status', 'published');
});

test('a super admin can archive a course', function () {
    $admin = actingAsSuperAdmin($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);

    $this->withHeaders(bearerHeaderFor($admin))
        ->postJson("/api/v1/admin/courses/{$course->id}/archive")
        ->assertOk()
        ->assertJsonPath('data.status', 'archived');
});

test('a student cannot moderate courses', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->create(['organization_id' => $this->organization->id]);

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/admin/courses/{$course->id}/publish")
        ->assertStatus(403);
});
