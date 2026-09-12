<?php

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
});

test('a super admin can list users in their organization', function () {
    $admin = actingAsSuperAdmin($this->organization);
    actingAsStudent($this->organization);
    actingAsStudent(Organization::factory()->create());

    $response = $this->withHeaders(bearerHeaderFor($admin))->getJson('/api/v1/admin/users');

    $response->assertOk()->assertJsonCount(2, 'data');
});

test('a super admin can change a user role', function () {
    $admin = actingAsSuperAdmin($this->organization);
    $student = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($admin))
        ->putJson("/api/v1/admin/users/{$student->id}", ['role' => 'teacher'])
        ->assertOk()
        ->assertJsonPath('data.roles.0', 'teacher');

    expect($student->fresh()->hasRole('teacher'))->toBeTrue();
});

test('a super admin can deactivate a user', function () {
    $admin = actingAsSuperAdmin($this->organization);
    $student = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($admin))
        ->deleteJson("/api/v1/admin/users/{$student->id}")
        ->assertOk();

    $this->assertSoftDeleted('users', ['id' => $student->id]);
});

test('a teacher cannot manage users', function () {
    $teacher = actingAsTeacher($this->organization);
    $student = User::factory()->create(['organization_id' => $this->organization->id]);

    $this->withHeaders(bearerHeaderFor($teacher))
        ->getJson('/api/v1/admin/users')
        ->assertStatus(403);

    $this->withHeaders(bearerHeaderFor($teacher))
        ->putJson("/api/v1/admin/users/{$student->id}", ['role' => 'teacher'])
        ->assertStatus(403);
});
