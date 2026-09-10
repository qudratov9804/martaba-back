<?php

use App\Models\Course;
use App\Models\Organization;

test('only published and public courses appear in the public listing', function () {
    $organization = Organization::factory()->create();
    Course::factory()->published()->create(['organization_id' => $organization->id]);
    Course::factory()->create(['organization_id' => $organization->id]); // draft, private

    $response = $this->getJson('/api/v1/public/courses?organization='.$organization->slug);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});

test('a draft course is not visible via its public show endpoint', function () {
    $organization = Organization::factory()->create();
    $course = Course::factory()->create(['organization_id' => $organization->id]);

    $this->getJson("/api/v1/public/courses/{$course->slug}?organization=".$organization->slug)
        ->assertStatus(404);
});

test('a published course is visible via its public show endpoint', function () {
    $organization = Organization::factory()->create();
    $course = Course::factory()->published()->create(['organization_id' => $organization->id]);

    $this->getJson("/api/v1/public/courses/{$course->slug}?organization=".$organization->slug)
        ->assertOk()
        ->assertJsonPath('data.slug', $course->slug);
});
