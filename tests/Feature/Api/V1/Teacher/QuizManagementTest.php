<?php

use App\Models\Course;
use App\Models\Organization;
use App\Models\Quiz;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
    $this->teacher = actingAsTeacher($this->organization);
    $this->course = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $this->teacher->id,
    ]);
    $this->course->courseInstructors()->create([
        'user_id' => $this->teacher->id, 'role' => 'instructor', 'is_primary' => true, 'created_at' => now(),
    ]);
});

test('a teacher can create a quiz with questions and options', function () {
    $quizResponse = $this->withHeaders(bearerHeaderFor($this->teacher))
        ->postJson("/api/v1/teacher/courses/{$this->course->id}/quizzes", [
            'title' => 'Module 1 Quiz',
            'type' => 'lesson',
            'passing_score' => 80,
        ]);

    $quizResponse->assertCreated();
    $quizId = $quizResponse->json('data.id');

    $questionResponse = $this->withHeaders(bearerHeaderFor($this->teacher))
        ->postJson("/api/v1/teacher/quizzes/{$quizId}/questions", [
            'type' => 'single_choice',
            'question_text' => 'What is 2 + 2?',
            'points' => 2,
            'options' => [
                ['text' => '3', 'is_correct' => false],
                ['text' => '4', 'is_correct' => true],
            ],
        ]);

    $questionResponse->assertCreated();
    $this->assertDatabaseHas('question_options', ['text' => '4', 'is_correct' => true]);
});

test('a teacher cannot manage quizzes on a course they do not own', function () {
    $otherTeacher = actingAsTeacher($this->organization);
    $otherCourse = Course::factory()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $otherTeacher->id,
    ]);

    $this->withHeaders(bearerHeaderFor($this->teacher))
        ->postJson("/api/v1/teacher/courses/{$otherCourse->id}/quizzes", ['title' => 'Hijack', 'type' => 'lesson'])
        ->assertStatus(403);
});

test('a student cannot create a quiz', function () {
    $student = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/teacher/courses/{$this->course->id}/quizzes", ['title' => 'Blocked', 'type' => 'lesson'])
        ->assertStatus(403);
});

test('a teacher can update and delete their own quiz', function () {
    $quiz = Quiz::factory()->create(['course_id' => $this->course->id]);

    $this->withHeaders(bearerHeaderFor($this->teacher))
        ->putJson("/api/v1/teacher/quizzes/{$quiz->id}", ['title' => 'Renamed Quiz'])
        ->assertOk()
        ->assertJsonPath('data.title', 'Renamed Quiz');

    $this->withHeaders(bearerHeaderFor($this->teacher))
        ->deleteJson("/api/v1/teacher/quizzes/{$quiz->id}")
        ->assertOk();

    $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
});
