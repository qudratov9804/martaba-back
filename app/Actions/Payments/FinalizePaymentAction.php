<?php

namespace App\Actions\Payments;

use App\Actions\Enrollments\EnrollStudentAction;
use App\Enums\EnrollmentSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionType;
use App\Models\Payment;

/**
 * Shared payment finalization logic used by every gateway's webhook
 * handler, so that "mark succeeded -> pay the order -> enroll the
 * student" only has one implementation regardless of which provider
 * triggered it.
 */
class FinalizePaymentAction
{
    public function __construct(private readonly EnrollStudentAction $enrollStudentAction) {}

    public function succeed(Payment $payment): void
    {
        $payment->update(['status' => PaymentStatus::Succeeded, 'paid_at' => now()]);

        $payment->transactions()->create([
            'type' => PaymentTransactionType::Capture,
            'status' => PaymentStatus::Succeeded->value,
            'amount_minor' => $payment->amount_minor,
            'currency' => $payment->currency,
            'processed_at' => now(),
            'created_at' => now(),
        ]);

        $order = $payment->order;
        $order->update(['status' => OrderStatus::Paid, 'paid_at' => now()]);

        foreach ($order->items as $item) {
            $enrollment = $this->enrollStudentAction->handle($order->student, $item->course, EnrollmentSource::Purchase);
            $enrollment->update(['order_id' => $order->id]);
        }
    }

    /**
     * @param  array<string, mixed>  $rawPayload
     */
    public function fail(Payment $payment, array $rawPayload = []): void
    {
        $payment->update([
            'status' => PaymentStatus::Failed,
            'failed_at' => now(),
            'failure_code' => $rawPayload['failure_code'] ?? null,
            'failure_message' => $rawPayload['failure_message'] ?? null,
        ]);

        $payment->transactions()->create([
            'type' => PaymentTransactionType::Payment,
            'status' => PaymentStatus::Failed->value,
            'amount_minor' => $payment->amount_minor,
            'currency' => $payment->currency,
            'processed_at' => now(),
            'created_at' => now(),
        ]);
    }
}
