<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    config(['services.click.secret_key' => 'test-click-secret', 'services.click.service_id' => '12345']);

    $this->organization = Organization::factory()->create();
    $this->course = Course::factory()->published()->paid(10000)->create(['organization_id' => $this->organization->id]);
    $this->student = actingAsStudent($this->organization);

    $orderResponse = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson('/api/v1/checkout/orders', ['course_id' => $this->course->id]);
    $orderId = $orderResponse->json('data.id');

    $paymentResponse = $this->withHeaders(bearerHeaderFor($this->student))
        ->postJson("/api/v1/orders/{$orderId}/payments", ['provider' => 'click']);

    $this->payment = Payment::findOrFail($paymentResponse->json('data.id'));
});

function clickSignature(array $data, bool $includesPrepareId): string
{
    $parts = [
        $data['click_trans_id'],
        $data['service_id'],
        config('services.click.secret_key'),
        $data['merchant_trans_id'],
    ];

    if ($includesPrepareId) {
        $parts[] = $data['merchant_prepare_id'];
    }

    $parts[] = $data['amount'];
    $parts[] = $data['action'];
    $parts[] = $data['sign_time'];

    return md5(implode('', $parts));
}

test('creating a click payment returns a checkout url', function () {
    expect($this->payment->fresh()->metadata_json['checkout_url'] ?? null)->toContain('service_id=12345');
});

test('click prepare succeeds with a valid signature and matching amount', function () {
    $data = [
        'click_trans_id' => '111',
        'service_id' => '12345',
        'merchant_trans_id' => $this->payment->id,
        'amount' => '10000.00',
        'action' => 0,
        'sign_time' => '2026-01-01 00:00:00',
    ];
    $data['sign_string'] = clickSignature($data, includesPrepareId: false);

    $response = $this->postJson('/api/v1/webhooks/payments/click', $data);

    $response->assertOk()
        ->assertJsonPath('error', 0)
        ->assertJsonPath('merchant_prepare_id', $this->payment->id);

    $this->assertDatabaseHas('payments', ['id' => $this->payment->id, 'provider_payment_id' => '111']);
});

test('click prepare rejects an invalid signature', function () {
    $data = [
        'click_trans_id' => '111',
        'service_id' => '12345',
        'merchant_trans_id' => $this->payment->id,
        'amount' => '10000.00',
        'action' => 0,
        'sign_time' => '2026-01-01 00:00:00',
        'sign_string' => 'wrong',
    ];

    $this->postJson('/api/v1/webhooks/payments/click', $data)
        ->assertOk()
        ->assertJsonPath('error', -1);
});

test('click prepare rejects a mismatched amount', function () {
    $data = [
        'click_trans_id' => '111',
        'service_id' => '12345',
        'merchant_trans_id' => $this->payment->id,
        'amount' => '1.00',
        'action' => 0,
        'sign_time' => '2026-01-01 00:00:00',
    ];
    $data['sign_string'] = clickSignature($data, includesPrepareId: false);

    $this->postJson('/api/v1/webhooks/payments/click', $data)
        ->assertOk()
        ->assertJsonPath('error', -2);
});

test('click complete after prepare marks the payment paid and enrolls the student', function () {
    $prepareData = [
        'click_trans_id' => '222',
        'service_id' => '12345',
        'merchant_trans_id' => $this->payment->id,
        'amount' => '10000.00',
        'action' => 0,
        'sign_time' => '2026-01-01 00:00:00',
    ];
    $prepareData['sign_string'] = clickSignature($prepareData, includesPrepareId: false);
    $this->postJson('/api/v1/webhooks/payments/click', $prepareData)->assertOk();

    $completeData = [
        'click_trans_id' => '222',
        'service_id' => '12345',
        'merchant_trans_id' => $this->payment->id,
        'merchant_prepare_id' => $this->payment->id,
        'amount' => '10000.00',
        'action' => 1,
        'error' => 0,
        'sign_time' => '2026-01-01 00:00:01',
    ];
    $completeData['sign_string'] = clickSignature($completeData, includesPrepareId: true);

    $response = $this->postJson('/api/v1/webhooks/payments/click', $completeData);

    $response->assertOk()->assertJsonPath('error', 0);

    $this->assertDatabaseHas('payments', ['id' => $this->payment->id, 'status' => 'succeeded']);
    $this->assertDatabaseHas('enrollments', [
        'course_id' => $this->course->id,
        'student_id' => $this->student->id,
        'status' => 'active',
    ]);

    $replay = $this->postJson('/api/v1/webhooks/payments/click', $completeData);
    $replay->assertOk()->assertJsonPath('error', -4);

    expect(Enrollment::where('course_id', $this->course->id)->where('student_id', $this->student->id)->count())->toBe(1);
    expect(PaymentWebhook::where('provider', 'click')->count())->toBe(2);
});
