<?php

namespace App\Actions\Payments;

use App\Actions\Enrollments\EnrollStudentAction;
use App\Enums\EnrollmentSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionType;
use App\Enums\WebhookStatus;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;

class ProcessPaymentWebhookAction
{
    public function __construct(
        private readonly PaymentGatewayManager $gatewayManager,
        private readonly EnrollStudentAction $enrollStudentAction,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(PaymentProvider $provider, array $payload, ?string $signature): PaymentWebhook
    {
        $gateway = $this->gatewayManager->gateway($provider);
        $event = $gateway->parseWebhook($payload, $signature);

        $existing = PaymentWebhook::where('provider', $provider->value)
            ->where('event_id', $event->eventId)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($provider, $payload, $signature, $event) {
            $webhook = PaymentWebhook::create([
                'provider' => $provider->value,
                'event_id' => $event->eventId,
                'event_type' => $event->eventType,
                'signature' => $signature,
                'payload_json' => $payload,
                'status' => WebhookStatus::Pending,
                'created_at' => now(),
            ]);

            $payment = $event->providerPaymentId
                ? Payment::where('provider', $provider->value)
                    ->where('provider_payment_id', $event->providerPaymentId)
                    ->lockForUpdate()
                    ->first()
                : null;

            if (! $payment) {
                $webhook->update(['status' => WebhookStatus::Failed, 'error_message' => 'Payment not found for this event.']);

                return $webhook;
            }

            if ($payment->status === PaymentStatus::Succeeded) {
                $webhook->update(['status' => WebhookStatus::Ignored, 'processed_at' => now()]);

                return $webhook;
            }

            if ($event->success) {
                $this->markPaymentSucceeded($payment);
            } else {
                $this->markPaymentFailed($payment, $event->rawPayload);
            }

            $webhook->update(['status' => WebhookStatus::Processed, 'processed_at' => now()]);

            return $webhook;
        });
    }

    private function markPaymentSucceeded(Payment $payment): void
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
    private function markPaymentFailed(Payment $payment, array $rawPayload): void
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
