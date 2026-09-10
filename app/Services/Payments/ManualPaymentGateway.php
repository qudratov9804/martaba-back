<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\Payments\WebhookEventData;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Exceptions\InvalidWebhookSignatureException;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

/**
 * A fully working reference gateway used for local development, testing,
 * and organizations that reconcile payments manually (e.g. bank transfer).
 * Real providers (Click, Payme, Uzum, Stripe, PayPal) implement the same
 * PaymentGatewayInterface and are registered in PaymentGatewayManager once
 * their API credentials and signature verification rules are available.
 */
class ManualPaymentGateway implements PaymentGatewayInterface
{
    public function createPayment(Order $order): Payment
    {
        return Payment::create([
            'organization_id' => $order->organization_id,
            'order_id' => $order->id,
            'student_id' => $order->student_id,
            'provider' => PaymentProvider::Manual,
            'status' => PaymentStatus::Pending,
            'amount_minor' => $order->total_minor,
            'currency' => $order->currency,
            'provider_payment_id' => 'manual_'.Str::random(24),
            'idempotency_key' => (string) Str::uuid(),
        ]);
    }

    public function verifyPayment(Payment $payment): bool
    {
        return $payment->status === PaymentStatus::Succeeded;
    }

    public function parseWebhook(array $payload, ?string $signature): WebhookEventData
    {
        if (! hash_equals((string) config('services.manual_gateway.webhook_secret'), (string) $signature)) {
            throw new InvalidWebhookSignatureException;
        }

        return new WebhookEventData(
            eventId: (string) ($payload['event_id'] ?? ''),
            eventType: (string) ($payload['event_type'] ?? ''),
            providerPaymentId: $payload['provider_payment_id'] ?? null,
            success: ($payload['event_type'] ?? null) === 'payment.succeeded',
            rawPayload: $payload,
        );
    }

    public function refund(Payment $payment, int $amountMinor, ?string $reason = null): array
    {
        return [
            'provider_refund_id' => 'manual_refund_'.Str::random(24),
            'succeeded' => true,
        ];
    }
}
