<?php

use App\Models\Category;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
});

test('a super admin can create a category', function () {
    $admin = actingAsSuperAdmin($this->organization);

    $response = $this->withHeaders(bearerHeaderFor($admin))->postJson('/api/v1/admin/categories', [
        'name' => 'Web Development',
        'slug' => 'web-development',
    ]);

    $response->assertCreated()->assertJsonPath('data.slug', 'web-development');
    $this->assertDatabaseHas('categories', ['slug' => 'web-development', 'organization_id' => $this->organization->id]);
});

test('a category can have nested children', function () {
    $admin = actingAsSuperAdmin($this->organization);
    $parent = Category::factory()->create(['organization_id' => $this->organization->id]);

    $response = $this->withHeaders(bearerHeaderFor($admin))->postJson('/api/v1/admin/categories', [
        'name' => 'Child Category',
        'slug' => 'child-category',
        'parent_id' => $parent->id,
    ]);

    $response->assertCreated()->assertJsonPath('data.parent_id', $parent->id);

    $listResponse = $this->withHeaders(bearerHeaderFor($admin))->getJson('/api/v1/admin/categories');
    $listResponse->assertOk();
    expect($listResponse->json('data.0.children'))->toHaveCount(1);
});

test('category slugs must be unique per organization', function () {
    $admin = actingAsSuperAdmin($this->organization);
    Category::factory()->create(['organization_id' => $this->organization->id, 'slug' => 'design']);

    $response = $this->withHeaders(bearerHeaderFor($admin))->postJson('/api/v1/admin/categories', [
        'name' => 'Design',
        'slug' => 'design',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['slug']);
});

test('a teacher cannot create a category', function () {
    $teacher = actingAsTeacher($this->organization);

    $response = $this->withHeaders(bearerHeaderFor($teacher))->postJson('/api/v1/admin/categories', [
        'name' => 'Blocked',
        'slug' => 'blocked',
    ]);

    $response->assertStatus(403);
});

test('the public can browse active categories without authentication', function () {
    Category::factory()->create(['organization_id' => $this->organization->id, 'status' => 'active']);
    Category::factory()->create(['organization_id' => $this->organization->id, 'status' => 'inactive']);

    $response = $this->getJson('/api/v1/public/categories?organization='.$this->organization->slug);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});
