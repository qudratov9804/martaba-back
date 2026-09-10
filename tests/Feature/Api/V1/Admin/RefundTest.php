<?php

use App\Models\Course;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
    $this->admin = actingAsSuperAdmin($this->organization);
    $this->course = Course::factory()->published()->paid(10000)->create(['organization_id' => $this->organization->id]);
    $this->student = actingAsStudent($this->organization);

    $orderResponse = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/orders', ['course_id' => $this->course->id]);
    $orderId = $orderResponse->json('data.id');

    $paymentResponse = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/orders/{$orderId}/payments", ['provider' => 'manual']);
    $this->providerPaymentId = $paymentResponse->json('data.provider_payment_id');
    $this->paymentId = $paymentResponse->json('data.id');
    $this->orderId = $orderId;

    $this->postJson('/api/v1/webhooks/payments/manual', [
        'event_id' => 'evt_refund_setup',
        'event_type' => 'payment.succeeded',
        'provider_payment_id' => $this->providerPaymentId,
    ], ['X-Webhook-Signature' => (string) config('services.manual_gateway.webhook_secret')]);
});

test('a super admin can fully refund a succeeded payment', function () {
    $response = $this->withHeaders(bearerHeaderFor($this->admin))
        ->postJson("/api/v1/admin/payments/{$this->paymentId}/refund", [
            'amount_minor' => 10000,
            'reason' => 'Customer request',
        ]);

    $response->assertCreated()->assertJsonPath('data.status', 'succeeded');

    $this->assertDatabaseHas('payments', ['id' => $this->paymentId, 'status' => 'refunded']);
    $this->assertDatabaseHas('orders', ['id' => $this->orderId, 'status' => 'refunded']);
});

test('a refund exceeding the payment amount is rejected', function () {
    $this->withHeaders(bearerHeaderFor($this->admin))
        ->postJson("/api/v1/admin/payments/{$this->paymentId}/refund", ['amount_minor' => 999999])
        ->assertStatus(422);
});

test('a partial refund updates the payment to partially refunded', function () {
    $response = $this->withHeaders(bearerHeaderFor($this->admin))
        ->postJson("/api/v1/admin/payments/{$this->paymentId}/refund", ['amount_minor' => 4000]);

    $response->assertCreated();

    $this->assertDatabaseHas('payments', ['id' => $this->paymentId, 'status' => 'partially_refunded']);
    $this->assertDatabaseHas('orders', ['id' => $this->orderId, 'status' => 'partially_refunded']);
});

test('a student cannot issue a refund', function () {
    $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/admin/payments/{$this->paymentId}/refund", ['amount_minor' => 10000])
        ->assertStatus(403);
});
