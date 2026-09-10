<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
    $this->teacher = actingAsTeacher($this->organization);
    $this->course = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $this->teacher->id,
    ]);
});

test('a teacher can add a section to their course', function () {
    $response = $this->withHeaders(bearerHeaderFor($this->teacher))
        ->postJson("/api/v1/teacher/courses/{$this->course->id}/sections", ['title' => 'Getting Started']);

    $response->assertCreated()->assertJsonPath('data.title', 'Getting Started');
    $this->assertDatabaseHas('course_sections', ['course_id' => $this->course->id, 'title' => 'Getting Started']);
});

test('a teacher can add a lesson to a section', function () {
    $section = CourseSection::factory()->create(['course_id' => $this->course->id]);

    $response = $this->withHeaders(bearerHeaderFor($this->teacher))
        ->postJson("/api/v1/teacher/sections/{$section->id}/lessons", [
            'title' => 'Introduction',
            'slug' => 'introduction',
            'lesson_type' => 'video',
        ]);

    $response->assertCreated()->assertJsonPath('data.lesson_type', 'video');
    $this->assertDatabaseHas('course_lessons', ['section_id' => $section->id, 'slug' => 'introduction']);
});

test('a teacher can add content blocks to a lesson', function () {
    $section = CourseSection::factory()->create(['course_id' => $this->course->id]);
    $lesson = CourseLesson::factory()->create(['course_id' => $this->course->id, 'section_id' => $section->id]);

    $response = $this->withHeaders(bearerHeaderFor($this->teacher))
        ->postJson("/api/v1/teacher/lessons/{$lesson->id}/contents", [
            'content_type' => 'text',
            'text_content' => 'Welcome to the course!',
        ]);

    $response->assertCreated()->assertJsonPath('data.text_content', 'Welcome to the course!');
});

test('a teacher can reorder sections within their course', function () {
    $first = CourseSection::factory()->create(['course_id' => $this->course->id, 'sort_order' => 0]);
    $second = CourseSection::factory()->create(['course_id' => $this->course->id, 'sort_order' => 1]);

    $response = $this->withHeaders(bearerHeaderFor($this->teacher))
        ->postJson("/api/v1/teacher/courses/{$this->course->id}/sections/reorder", [
            'section_ids' => [$second->id, $first->id],
        ]);

    $response->assertOk();
    expect($second->fresh()->sort_order)->toBe(0);
    expect($first->fresh()->sort_order)->toBe(1);
});

test('a teacher cannot manage sections on a course they do not own', function () {
    $otherTeacher = actingAsTeacher($this->organization);
    $otherCourse = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $otherTeacher->id,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($this->teacher))
        ->postJson("/api/v1/teacher/courses/{$otherCourse->id}/sections", ['title' => 'Hijack']);

    $response->assertStatus(403);
});
