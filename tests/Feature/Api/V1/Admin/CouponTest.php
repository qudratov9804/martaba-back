<?php

use App\Models\Coupon;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
});

test('a super admin can create a coupon', function () {
    $admin = actingAsSuperAdmin($this->organization);

    $response = $this->withHeaders(bearerHeaderFor($admin))->postJson('/api/v1/admin/coupons', [
        'code' => 'WELCOME10',
        'name' => 'Welcome Discount',
        'type' => 'percentage',
        'value' => 10,
        'scope_type' => 'all',
    ]);

    $response->assertCreated()->assertJsonPath('data.code', 'WELCOME10');
});

test('percentage coupon value cannot exceed 100', function () {
    $admin = actingAsSuperAdmin($this->organization);

    $this->withHeaders(bearerHeaderFor($admin))->postJson('/api/v1/admin/coupons', [
        'code' => 'TOOBIG',
        'name' => 'Too Big',
        'type' => 'percentage',
        'value' => 150,
        'scope_type' => 'all',
    ])->assertStatus(422)->assertJsonValidationErrors(['value']);
});

test('a student cannot manage coupons', function () {
    $student = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($student))->postJson('/api/v1/admin/coupons', [
        'code' => 'BLOCKED',
        'name' => 'Blocked',
        'type' => 'fixed',
        'value' => 500,
        'scope_type' => 'all',
    ])->assertStatus(403);
});

test('a super admin can update and delete a coupon', function () {
    $admin = actingAsSuperAdmin($this->organization);
    $coupon = Coupon::factory()->create(['organization_id' => $this->organization->id]);

    $this->withHeaders(bearerHeaderFor($admin))
        ->putJson("/api/v1/admin/coupons/{$coupon->id}", ['name' => 'Renamed'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Renamed');

    $this->withHeaders(bearerHeaderFor($admin))
        ->deleteJson("/api/v1/admin/coupons/{$coupon->id}")
        ->assertOk();

    $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
});
