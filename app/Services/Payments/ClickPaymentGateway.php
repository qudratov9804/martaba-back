<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\Payments\WebhookEventData;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Click (click.uz) Merchant API adapter. Click does not have a single
 * webhook callback like most providers: it calls the merchant back twice
 * per transaction (Prepare, then Complete), and expects its own response
 * shape rather than the application's standard JSON envelope. That two-step
 * handshake is handled by ClickWebhookController + ClickSignature, not by
 * parseWebhook() here — see ClickWebhookController for the authoritative
 * flow. Signature and error-code conventions follow Click's published
 * Merchant API v2 spec (docs.click.uz); re-verify against current docs
 * before relying on this in production, since providers revise these.
 */
class ClickPaymentGateway implements PaymentGatewayInterface
{
    public function createPayment(Order $order): Payment
    {
        $payment = Payment::create([
            'organization_id' => $order->organization_id,
            'order_id' => $order->id,
            'student_id' => $order->student_id,
            'provider' => PaymentProvider::Click,
            'status' => PaymentStatus::Pending,
            'amount_minor' => $order->total_minor,
            'currency' => $order->currency,
            'idempotency_key' => (string) Str::uuid(),
        ]);

        $payment->update(['metadata_json' => ['checkout_url' => $this->buildCheckoutUrl($payment)]]);

        return $payment;
    }

    public function verifyPayment(Payment $payment): bool
    {
        return $payment->status === PaymentStatus::Succeeded;
    }

    public function parseWebhook(array $payload, ?string $signature): WebhookEventData
    {
        throw new RuntimeException(
            'Click does not use the generic webhook endpoint. It is handled by ClickWebhookController via a dedicated prepare/complete flow.'
        );
    }

    public function refund(Payment $payment, int $amountMinor, ?string $reason = null): array
    {
        $clickTransId = $payment->provider_transaction_id ?? $payment->provider_payment_id;

        if (! $clickTransId) {
            throw new RuntimeException('This payment has no Click transaction id to reverse.');
        }

        $response = Http::withHeaders(['Auth' => $this->buildAuthHeader()])
            ->delete(config('services.click.api_url')."/payment/reversal/{$clickTransId}");

        $body = $response->json();

        if (! $response->successful() || (int) ($body['error_code'] ?? $body['error'] ?? -1) !== 0) {
            throw new RuntimeException('Click refund failed: '.($body['error_note'] ?? $response->body()));
        }

        return [
            'provider_refund_id' => (string) ($body['payment_id'] ?? $clickTransId),
            'succeeded' => true,
        ];
    }

    private function buildCheckoutUrl(Payment $payment): string
    {
        $amount = number_format($payment->amount_minor, 2, '.', '');

        $query = http_build_query([
            'service_id' => config('services.click.service_id'),
            'merchant_id' => config('services.click.merchant_id'),
            'amount' => $amount,
            'transaction_param' => $payment->id,
            'return_url' => config('services.click.return_url', config('app.url')),
        ]);

        return config('services.click.checkout_url').'?'.$query;
    }

    private function buildAuthHeader(): string
    {
        $timestamp = time();
        $digest = sha1($timestamp.config('services.click.secret_key'));

        return config('services.click.merchant_user_id').":{$digest}:{$timestamp}";
    }
}
