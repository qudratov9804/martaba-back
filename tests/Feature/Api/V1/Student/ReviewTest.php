<?php

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\Review;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
});

test('an enrolled student can review a course', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $course->id,
        'student_id' => $student->id,
        'status' => EnrollmentStatus::Active,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/courses/{$course->id}/reviews", [
            'rating' => 5,
            'title' => 'Great course',
            'comment' => 'Learned a lot.',
        ]);

    $response->assertCreated()->assertJsonPath('data.rating', 5);

    $this->assertDatabaseHas('reviews', [
        'course_id' => $course->id,
        'student_id' => $student->id,
        'rating' => 5,
    ]);

    expect($course->fresh()->rating_average)->toBe(5.0);
    expect($course->fresh()->rating_count)->toBe(1);
});

test('reviews cannot be submitted when disabled for the course', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create([
        'organization_id' => $this->organization->id,
        'reviews_enabled' => false,
    ]);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $course->id,
        'student_id' => $student->id,
        'status' => EnrollmentStatus::Active,
    ]);

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/courses/{$course->id}/reviews", ['rating' => 5])
        ->assertStatus(422);
});

test('a student who is not enrolled cannot review a course', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/courses/{$course->id}/reviews", ['rating' => 4])
        ->assertStatus(422);
});

test('a student cannot review the same course twice', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $course->id,
        'student_id' => $student->id,
        'status' => EnrollmentStatus::Active,
    ]);

    $this->withHeaders(bearerHeaderFor($student))->postJson("/api/v1/student/courses/{$course->id}/reviews", ['rating' => 4]);

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/courses/{$course->id}/reviews", ['rating' => 3])
        ->assertStatus(422);

    expect(Review::where('course_id', $course->id)->count())->toBe(1);
});

test('a student can update and delete their own review', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $course->id,
        'student_id' => $student->id,
        'status' => EnrollmentStatus::Active,
    ]);

    $review = Review::factory()->create(['course_id' => $course->id, 'student_id' => $student->id, 'rating' => 3]);

    $this->withHeaders(bearerHeaderFor($student))
        ->putJson("/api/v1/student/reviews/{$review->id}", ['rating' => 5, 'comment' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.rating', 5);

    $this->withHeaders(bearerHeaderFor($student))
        ->deleteJson("/api/v1/student/reviews/{$review->id}")
        ->assertOk();

    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

test('a student cannot update another student review', function () {
    $owner = actingAsStudent($this->organization);
    $other = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);
    $review = Review::factory()->create(['course_id' => $course->id, 'student_id' => $owner->id]);

    $this->withHeaders(bearerHeaderFor($other))
        ->putJson("/api/v1/student/reviews/{$review->id}", ['rating' => 1])
        ->assertStatus(403);
});
