<?php

use App\Enums\RoleName;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('a super admin can list organizations', function () {
    $admin = actingAsSuperAdmin();
    Organization::factory()->count(3)->create();

    $response = $this->withHeaders(bearerHeaderFor($admin))->getJson('/api/v1/admin/organizations');

    $response->assertOk()->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(3);
});

test('a super admin can create an organization', function () {
    $admin = actingAsSuperAdmin();

    $response = $this->withHeaders(bearerHeaderFor($admin))->postJson('/api/v1/admin/organizations', [
        'name' => 'Acme Academy',
        'slug' => 'acme-academy',
        'email' => 'contact@acme.test',
    ]);

    $response->assertCreated()->assertJsonPath('data.slug', 'acme-academy');
    $this->assertDatabaseHas('organizations', ['slug' => 'acme-academy']);
});

test('creating an organization requires a unique slug', function () {
    $admin = actingAsSuperAdmin();
    Organization::factory()->create(['slug' => 'taken-slug']);

    $response = $this->withHeaders(bearerHeaderFor($admin))->postJson('/api/v1/admin/organizations', [
        'name' => 'Another Org',
        'slug' => 'taken-slug',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['slug']);
});

test('a student cannot create an organization', function () {
    $student = User::factory()->create();
    $student->assignRole(RoleName::Student->value);

    $response = $this->withHeaders(bearerHeaderFor($student))->postJson('/api/v1/admin/organizations', [
        'name' => 'Blocked Org',
        'slug' => 'blocked-org',
    ]);

    $response->assertStatus(403)->assertJson(['success' => false]);
    $this->assertDatabaseMissing('organizations', ['slug' => 'blocked-org']);
});

test('a guest cannot access the organizations api', function () {
    $this->getJson('/api/v1/admin/organizations')->assertStatus(401);
});

test('a super admin can update an organization', function () {
    $admin = actingAsSuperAdmin();
    $organization = Organization::factory()->create();

    $response = $this->withHeaders(bearerHeaderFor($admin))
        ->putJson("/api/v1/admin/organizations/{$organization->id}", ['name' => 'Renamed Org']);

    $response->assertOk()->assertJsonPath('data.name', 'Renamed Org');
});

test('a super admin can delete an organization', function () {
    $admin = actingAsSuperAdmin();
    $organization = Organization::factory()->create();

    $response = $this->withHeaders(bearerHeaderFor($admin))
        ->deleteJson("/api/v1/admin/organizations/{$organization->id}");

    $response->assertOk()->assertJson(['success' => true]);
    $this->assertSoftDeleted('organizations', ['id' => $organization->id]);
});
