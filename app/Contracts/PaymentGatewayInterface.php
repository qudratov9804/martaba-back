<?php

namespace App\Contracts;

use App\DTOs\Payments\WebhookEventData;
use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a pending payment for an order with this provider.
     */
    public function createPayment(Order $order): Payment;

    /**
     * Ask the provider to confirm the current status of a payment.
     */
    public function verifyPayment(Payment $payment): bool;

    /**
     * Parse and validate an inbound webhook payload into a normalized event.
     *
     * @param  array<string, mixed>  $payload
     */
    public function parseWebhook(array $payload, ?string $signature): WebhookEventData;

    /**
     * Ask the provider to refund a previously succeeded payment.
     *
     * @return array{provider_refund_id: string, succeeded: bool}
     */
    public function refund(Payment $payment, int $amountMinor, ?string $reason = null): array;
}
