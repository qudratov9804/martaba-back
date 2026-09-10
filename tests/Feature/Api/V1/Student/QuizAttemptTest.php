<?php

use App\Enums\EnrollmentSource;
use App\Enums\EnrollmentStatus;
use App\Enums\QuestionType;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
    $this->course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);
    $this->quiz = Quiz::factory()->create(['course_id' => $this->course->id, 'max_attempts' => null]);

    $this->singleChoice = Question::factory()->create([
        'quiz_id' => $this->quiz->id,
        'organization_id' => $this->organization->id,
        'type' => QuestionType::SingleChoice,
        'points' => 2,
    ]);
    $this->correctOption = $this->singleChoice->options()->create(['text' => 'Correct', 'is_correct' => true, 'sort_order' => 0]);
    $this->singleChoice->options()->create(['text' => 'Wrong', 'is_correct' => false, 'sort_order' => 1]);

    $this->shortAnswer = Question::factory()->create([
        'quiz_id' => $this->quiz->id,
        'organization_id' => $this->organization->id,
        'type' => QuestionType::ShortAnswer,
        'points' => 1,
    ]);
    $this->shortAnswer->options()->create(['text' => 'Paris', 'is_correct' => true, 'sort_order' => 0]);

    $this->longAnswer = Question::factory()->create([
        'quiz_id' => $this->quiz->id,
        'organization_id' => $this->organization->id,
        'type' => QuestionType::LongAnswer,
        'points' => 5,
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

test('a non enrolled student cannot start a quiz attempt', function () {
    $outsider = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($outsider))
        ->postJson("/api/v1/student/quizzes/{$this->quiz->id}/attempts")
        ->assertStatus(403);
});

test('a student can start, answer, and submit a quiz with mixed auto and manual grading', function () {
    $start = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quizzes/{$this->quiz->id}/attempts");

    $start->assertCreated()->assertJsonPath('data.status', 'started');
    $attemptId = $start->json('data.id');

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/answer", [
            'question_id' => $this->singleChoice->id,
            'selected_option_ids' => [$this->correctOption->id],
        ])->assertOk();

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/answer", [
            'question_id' => $this->shortAnswer->id,
            'text_answer' => 'paris',
        ])->assertOk();

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/answer", [
            'question_id' => $this->longAnswer->id,
            'text_answer' => 'A thoughtful essay response.',
        ])->assertOk();

    $submit = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/submit");

    $submit->assertOk()
        ->assertJsonPath('data.status', 'submitted')
        ->assertJsonPath('data.score', 3);

    $this->assertDatabaseHas('quiz_attempts', ['id' => $attemptId, 'status' => 'submitted']);
});

test('a teacher grading the manual answer completes the attempt', function () {
    $teacher = actingAsTeacher($this->organization);
    $this->course->update(['created_by' => $teacher->id]);
    $this->course->courseInstructors()->create(['user_id' => $teacher->id, 'role' => 'instructor', 'is_primary' => true, 'created_at' => now()]);

    $start = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quizzes/{$this->quiz->id}/attempts");
    $attemptId = $start->json('data.id');

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/answer", [
            'question_id' => $this->singleChoice->id,
            'selected_option_ids' => [$this->correctOption->id],
        ]);
    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/answer", [
            'question_id' => $this->shortAnswer->id,
            'text_answer' => 'Paris',
        ]);
    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/answer", [
            'question_id' => $this->longAnswer->id,
            'text_answer' => 'Essay text.',
        ]);
    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/submit");

    $longAnswerRecord = QuizAnswer::where('quiz_attempt_id', $attemptId)
        ->where('question_id', $this->longAnswer->id)
        ->firstOrFail();

    $grade = $this->withHeaders(bearerHeaderFor($teacher))
        ->postJson("/api/v1/teacher/quiz-answers/{$longAnswerRecord->id}/grade", ['points_awarded' => 5]);

    $grade->assertOk()->assertJsonPath('data.points_awarded', 5);

    $this->assertDatabaseHas('quiz_attempts', ['id' => $attemptId, 'status' => 'graded', 'score' => 8.0]);
});

test('a student cannot exceed the quizzes max attempts', function () {
    $this->quiz->update(['max_attempts' => 1]);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quizzes/{$this->quiz->id}/attempts")
        ->assertCreated();

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quizzes/{$this->quiz->id}/attempts")
        ->assertStatus(422);
});

test('a student cannot view or submit another students attempt', function () {
    $otherStudent = actingAsStudent($this->organization);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $this->course->id,
        'student_id' => $otherStudent->id,
        'status' => EnrollmentStatus::Active,
    ]);

    $start = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/quizzes/{$this->quiz->id}/attempts");
    $attemptId = $start->json('data.id');

    $this->withHeaders(bearerHeaderFor($otherStudent))
        ->getJson("/api/v1/student/quiz-attempts/{$attemptId}")
        ->assertStatus(403);

    $this->withHeaders(bearerHeaderFor($otherStudent))
        ->postJson("/api/v1/student/quiz-attempts/{$attemptId}/submit")
        ->assertStatus(403);
});
