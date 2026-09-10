<?php

use App\Enums\EnrollmentSource;
use App\Enums\EnrollmentStatus;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');

    $this->organization = Organization::factory()->create();
    $this->teacher = actingAsTeacher($this->organization);
    $this->course = Course::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $this->teacher->id,
        'certificate_enabled' => true,
    ]);
    $this->course->courseInstructors()->create([
        'user_id' => $this->teacher->id, 'role' => 'instructor', 'is_primary' => true, 'created_at' => now(),
    ]);
    $this->section = CourseSection::factory()->create(['course_id' => $this->course->id]);
    $this->lesson = CourseLesson::factory()->create([
        'course_id' => $this->course->id,
        'section_id' => $this->section->id,
        'is_required' => true,
    ]);

    $this->student = actingAsStudent($this->organization);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $this->course->id,
        'student_id' => $this->student->id,
        'source' => EnrollmentSource::Free,
        'status' => EnrollmentStatus::Active,
    ]);
});

test('completing all required lessons issues a certificate when certificates are enabled', function () {
    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/lessons/{$this->lesson->id}/complete")
        ->assertOk();

    $this->assertDatabaseHas('enrollments', [
        'course_id' => $this->course->id,
        'student_id' => $this->student->id,
        'status' => 'completed',
    ]);

    $this->assertDatabaseHas('certificates', [
        'course_id' => $this->course->id,
        'student_id' => $this->student->id,
        'status' => 'issued',
    ]);

    $certificate = Certificate::where('student_id', $this->student->id)->firstOrFail();
    Storage::disk('public')->assertExists($certificate->pdf_path);
    Storage::disk('public')->assertExists($certificate->qr_code_path);
});

test('no certificate is generated when the course does not have certificates enabled', function () {
    $this->course->update(['certificate_enabled' => false]);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/lessons/{$this->lesson->id}/complete")
        ->assertOk();

    $this->assertDatabaseHas('enrollments', ['course_id' => $this->course->id, 'status' => 'completed']);
    $this->assertDatabaseMissing('certificates', ['course_id' => $this->course->id]);
});

test('a course requiring a final quiz score does not complete until the quiz is passed', function () {
    $this->course->update(['completion_rules_json' => [
        'require_all_required_lessons' => true,
        'minimum_final_quiz_score' => 80,
    ]]);

    $quiz = Quiz::factory()->create(['course_id' => $this->course->id, 'type' => 'final']);
    $question = Question::factory()->create([
        'quiz_id' => $quiz->id,
        'organization_id' => $this->organization->id,
        'type' => 'single_choice',
        'points' => 1,
    ]);
    $correct = $question->options()->create(['text' => 'Right', 'is_correct' => true, 'sort_order' => 0]);
    $question->options()->create(['text' => 'Wrong', 'is_correct' => false, 'sort_order' => 1]);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/lessons/{$this->lesson->id}/complete")
        ->assertOk();

    $this->assertDatabaseHas('enrollments', ['course_id' => $this->course->id, 'status' => 'active']);

    $attemptResponse = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quizzes/{$quiz->id}/attempts");
    $attemptId = $attemptResponse->json('data.id');

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/answer", [
            'question_id' => $question->id,
            'selected_option_ids' => [$correct->id],
        ]);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/submit")
        ->assertOk();

    $this->assertDatabaseHas('enrollments', ['course_id' => $this->course->id, 'status' => 'completed']);
    $this->assertDatabaseHas('certificates', ['course_id' => $this->course->id, 'student_id' => $this->student->id]);
});

test('a student can list and view their own certificate', function () {
    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/lessons/{$this->lesson->id}/complete");

    $response = $this->withHeaders(bearerHeaderFor($this->student))->getJson('/api/v1/student/certificates');
    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);

    $certificateId = $response->json('data.0.id');
    $this->withHeaders(bearerHeaderFor($this->student))
        ->getJson("/api/v1/student/certificates/{$certificateId}")
        ->assertOk();
});

test('a student cannot view another students certificate', function () {
    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/lessons/{$this->lesson->id}/complete");

    $certificate = Certificate::where('student_id', $this->student->id)->firstOrFail();
    $otherStudent = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($otherStudent))
        ->getJson("/api/v1/student/certificates/{$certificate->id}")
        ->assertStatus(403);
});
