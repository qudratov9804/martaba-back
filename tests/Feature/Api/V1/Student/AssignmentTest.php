<?php

use App\Enums\EnrollmentSource;
use App\Enums\EnrollmentStatus;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
    $this->teacher = actingAsTeacher($this->organization);
    $this->course = Course::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'created_by' => $this->teacher->id,
    ]);
    $this->course->courseInstructors()->create([
        'user_id' => $this->teacher->id, 'role' => 'instructor', 'is_primary' => true, 'created_at' => now(),
    ]);
    $this->assignment = Assignment::factory()->create(['course_id' => $this->course->id, 'max_score' => 100]);

    $this->student = actingAsStudent($this->organization);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $this->course->id,
        'student_id' => $this->student->id,
        'source' => EnrollmentSource::Free,
        'status' => EnrollmentStatus::Active,
    ]);
});

test('a teacher can create an assignment for their course', function () {
    $response = $this->withHeaders(bearerHeaderFor($this->teacher))
        ->postJson("/api/v1/teacher/courses/{$this->course->id}/assignments", [
            'title' => 'Build a REST API',
            'max_score' => 100,
        ]);

    $response->assertCreated()->assertJsonPath('data.title', 'Build a REST API');
});

test('an enrolled student can submit an assignment', function () {
    $response = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/assignments/{$this->assignment->id}/submit", [
            'text_submission' => 'Here is my solution.',
        ]);

    $response->assertCreated()->assertJsonPath('data.status', 'submitted');
    $this->assertDatabaseHas('assignment_submissions', [
        'assignment_id' => $this->assignment->id,
        'student_id' => $this->student->id,
        'status' => 'submitted',
    ]);
});

test('a non enrolled student cannot submit an assignment', function () {
    $outsider = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($outsider))
        ->postJson("/api/v1/student/assignments/{$this->assignment->id}/submit", [
            'text_submission' => 'Sneaky submission.',
        ])->assertStatus(403);
});

test('submitting past the deadline is rejected unless late submission is allowed', function () {
    $this->assignment->update(['deadline' => now()->subDay(), 'allow_late_submission' => false]);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/assignments/{$this->assignment->id}/submit", ['text_submission' => 'Late work.'])
        ->assertStatus(422);
});

test('a late submission is accepted and flagged when allowed', function () {
    $this->assignment->update(['deadline' => now()->subDay(), 'allow_late_submission' => true]);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/assignments/{$this->assignment->id}/submit", ['text_submission' => 'Late work.'])
        ->assertCreated()
        ->assertJsonPath('data.status', 'late');
});

test('a teacher can grade a submission', function () {
    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/assignments/{$this->assignment->id}/submit", ['text_submission' => 'My work.']);

    $submission = AssignmentSubmission::where('assignment_id', $this->assignment->id)
        ->where('student_id', $this->student->id)
        ->firstOrFail();

    $response = $this->withHeaders(bearerHeaderFor($this->teacher))
        ->postJson("/api/v1/teacher/assignment-submissions/{$submission->id}/grade", [
            'score' => 92,
            'feedback' => 'Great work!',
        ]);

    $response->assertOk()->assertJsonPath('data.status', 'graded')->assertJsonPath('data.score', 92);
});

test('a student can only view their own submission', function () {
    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/student/assignments/{$this->assignment->id}/submit", ['text_submission' => 'My work.']);

    $submission = AssignmentSubmission::where('student_id', $this->student->id)->firstOrFail();
    $otherStudent = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($otherStudent))
        ->getJson("/api/v1/student/submissions/{$submission->id}")
        ->assertStatus(403);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->getJson("/api/v1/student/submissions/{$submission->id}")
        ->assertOk();
});
