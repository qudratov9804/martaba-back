<?php

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
    $this->course = Course::factory()->published()->paid(10000)->create(['organization_id' => $this->organization->id]);
    $this->student = actingAsStudent($this->organization);
});

test('checkout preview computes totals for a paid course without a coupon', function () {
    $response = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/preview', ['course_id' => $this->course->id]);

    $response->assertOk()
        ->assertJsonPath('data.subtotal_minor', 10000)
        ->assertJsonPath('data.discount_minor', 0)
        ->assertJsonPath('data.total_minor', 10000);
});

test('checkout preview applies a percentage coupon discount', function () {
    Coupon::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => 'SAVE10',
        'type' => 'percentage',
        'value' => 10,
    ]);

    $response = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/preview', ['course_id' => $this->course->id, 'coupon_code' => 'SAVE10']);

    $response->assertOk()
        ->assertJsonPath('data.discount_minor', 1000)
        ->assertJsonPath('data.total_minor', 9000);
});

test('an invalid coupon code is rejected', function () {
    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/preview', ['course_id' => $this->course->id, 'coupon_code' => 'NOPE'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['coupon_code']);
});

test('an expired coupon is rejected', function () {
    Coupon::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => 'EXPIRED',
        'ends_at' => now()->subDay(),
    ]);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/preview', ['course_id' => $this->course->id, 'coupon_code' => 'EXPIRED'])
        ->assertStatus(422);
});

test('a student can create an order for a paid course', function () {
    $response = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/orders', ['course_id' => $this->course->id]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.total_minor', 10000);

    $this->assertDatabaseHas('order_items', ['course_id' => $this->course->id, 'total_minor' => 10000]);
});

test('a student cannot create a second order for a course they are already enrolled in', function () {
    Enrollment::factory()->create([
        'organization_id' => $this->organization->id,
        'course_id' => $this->course->id,
        'student_id' => $this->student->id,
        'status' => 'active',
    ]);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/orders', ['course_id' => $this->course->id])
        ->assertStatus(422);
});

test('a coupon usage limit per user is enforced across separate orders', function () {
    $coupon = Coupon::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => 'ONCE',
        'usage_limit_per_user' => 1,
    ]);

    $courseTwo = Course::factory()->published()->paid(5000)->create(['organization_id' => $this->organization->id]);

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/orders', ['course_id' => $this->course->id, 'coupon_code' => 'ONCE'])
        ->assertCreated();

    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/orders', ['course_id' => $courseTwo->id, 'coupon_code' => 'ONCE'])
        ->assertStatus(422);
});
