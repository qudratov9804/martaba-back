<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\PaymentWebhook;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->organization = Organization::factory()->create();
    $this->course = Course::factory()->published()->paid(10000)->create(['organization_id' => $this->organization->id]);
    $this->student = actingAsStudent($this->organization);

    $orderResponse = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/orders', ['course_id' => $this->course->id]);
    $this->orderId = $orderResponse->json('data.id');
});

function webhookSignature(): string
{
    return (string) config('services.manual_gateway.webhook_secret');
}

test('a student can create a manual payment for their pending order', function () {
    $response = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/orders/{$this->orderId}/payments", ['provider' => 'manual']);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.amount_minor', 10000);

    $this->assertDatabaseHas('orders', ['id' => $this->orderId, 'status' => 'processing']);
});

test('a student cannot create a payment for another students order', function () {
    $outsider = actingAsStudent($this->organization);

    $this->withHeaders(bearerHeaderFor($outsider))
        ->postJson("/api/v1/orders/{$this->orderId}/payments", ['provider' => 'manual'])
        ->assertStatus(403);
});

test('a webhook without a valid signature is rejected', function () {
    $paymentResponse = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/orders/{$this->orderId}/payments", ['provider' => 'manual']);
    $providerPaymentId = $paymentResponse->json('data.provider_payment_id');

    $this->postJson('/api/v1/webhooks/payments/manual', [
        'event_id' => 'evt_1',
        'event_type' => 'payment.succeeded',
        'provider_payment_id' => $providerPaymentId,
    ], ['X-Webhook-Signature' => 'wrong-secret'])->assertStatus(401);
});

test('a succeeded webhook marks the payment and order paid and enrolls the student', function () {
    $paymentResponse = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/orders/{$this->orderId}/payments", ['provider' => 'manual']);
    $providerPaymentId = $paymentResponse->json('data.provider_payment_id');
    $paymentId = $paymentResponse->json('data.id');

    $webhook = $this->postJson('/api/v1/webhooks/payments/manual', [
        'event_id' => 'evt_success_1',
        'event_type' => 'payment.succeeded',
        'provider_payment_id' => $providerPaymentId,
    ], ['X-Webhook-Signature' => webhookSignature()]);

    $webhook->assertOk();

    $this->assertDatabaseHas('payments', ['id' => $paymentId, 'status' => 'succeeded']);
    $this->assertDatabaseHas('orders', ['id' => $this->orderId, 'status' => 'paid']);
    $this->assertDatabaseHas('enrollments', [
        'course_id' => $this->course->id,
        'student_id' => $this->student->id,
        'source' => 'purchase',
        'status' => 'active',
    ]);
});

test('replaying the same webhook event id does not duplicate the enrollment', function () {
    $paymentResponse = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/orders/{$this->orderId}/payments", ['provider' => 'manual']);
    $providerPaymentId = $paymentResponse->json('data.provider_payment_id');

    $payload = [
        'event_id' => 'evt_duplicate',
        'event_type' => 'payment.succeeded',
        'provider_payment_id' => $providerPaymentId,
    ];

    $this->postJson('/api/v1/webhooks/payments/manual', $payload, ['X-Webhook-Signature' => webhookSignature()])->assertOk();
    $this->postJson('/api/v1/webhooks/payments/manual', $payload, ['X-Webhook-Signature' => webhookSignature()])->assertOk();

    expect(Enrollment::where('course_id', $this->course->id)->where('student_id', $this->student->id)->count())->toBe(1);
    expect(PaymentWebhook::where('event_id', 'evt_duplicate')->count())->toBe(1);
});

test('a failed webhook marks the payment as failed without enrolling the student', function () {
    $paymentResponse = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/orders/{$this->orderId}/payments", ['provider' => 'manual']);
    $providerPaymentId = $paymentResponse->json('data.provider_payment_id');

    $this->postJson('/api/v1/webhooks/payments/manual', [
        'event_id' => 'evt_fail_1',
        'event_type' => 'payment.failed',
        'provider_payment_id' => $providerPaymentId,
        'failure_message' => 'Card declined.',
    ], ['X-Webhook-Signature' => webhookSignature()])->assertOk();

    $this->assertDatabaseHas('payments', ['provider_payment_id' => $providerPaymentId, 'status' => 'failed']);
    $this->assertDatabaseMissing('enrollments', ['course_id' => $this->course->id, 'student_id' => $this->student->id]);
});
