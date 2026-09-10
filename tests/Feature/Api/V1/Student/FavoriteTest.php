<?php

use App\Models\Course;
use App\Models\Favorite;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
});

test('a student can favorite and unfavorite a course', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/student/favorites/{$course->id}")
        ->assertCreated();

    $this->assertDatabaseHas('favorites', ['student_id' => $student->id, 'course_id' => $course->id]);

    $listResponse = $this->withHeaders(bearerHeaderFor($student))->getJson('/api/v1/student/favorites');
    $listResponse->assertOk();
    expect($listResponse->json('data'))->toHaveCount(1);

    $this->withHeaders(bearerHeaderFor($student))
        ->deleteJson("/api/v1/student/favorites/{$course->id}")
        ->assertOk();

    $this->assertDatabaseMissing('favorites', ['student_id' => $student->id, 'course_id' => $course->id]);
});

test('favoriting the same course twice does not create duplicates', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);

    $this->withHeaders(bearerHeaderFor($student))->postJson("/api/v1/student/favorites/{$course->id}");
    $this->withHeaders(bearerHeaderFor($student))->postJson("/api/v1/student/favorites/{$course->id}");

    expect(Favorite::where('student_id', $student->id)->count())->toBe(1);
});
