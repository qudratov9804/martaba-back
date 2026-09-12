<?php

use App\Models\Course;
use App\Models\Organization;
use App\Models\Review;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
});

test('a super admin can list and hide reviews for their organization', function () {
    $admin = actingAsSuperAdmin($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);
    $review = Review::factory()->create(['course_id' => $course->id, 'rating' => 5]);

    $this->withHeaders(bearerHeaderFor($admin))
        ->getJson('/api/v1/admin/reviews')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->withHeaders(bearerHeaderFor($admin))
        ->postJson("/api/v1/admin/reviews/{$review->id}/moderate", ['status' => 'hidden'])
        ->assertOk()
        ->assertJsonPath('data.status', 'hidden');

    expect($course->fresh()->rating_count)->toBe(0);
});

test('a student cannot moderate reviews', function () {
    $student = actingAsStudent($this->organization);
    $course = Course::factory()->published()->create(['organization_id' => $this->organization->id]);
    $review = Review::factory()->create(['course_id' => $course->id]);

    $this->withHeaders(bearerHeaderFor($student))
        ->postJson("/api/v1/admin/reviews/{$review->id}/moderate", ['status' => 'hidden'])
        ->assertStatus(403);
});
