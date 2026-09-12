<?php

namespace App\Actions\Payments;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\WebhookStatus;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;

class ProcessPaymentWebhookAction
{
    public function __construct(
        private readonly PaymentGatewayManager $gatewayManager,
        private readonly FinalizePaymentAction $finalizePaymentAction,
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
                $this->finalizePaymentAction->succeed($payment);
            } else {
                $this->finalizePaymentAction->fail($payment, $event->rawPayload);
            }

            $webhook->update(['status' => WebhookStatus::Processed, 'processed_at' => now()]);

            return $webhook;
        });
    }
}
