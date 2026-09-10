<?php

namespace App\DTOs\Payments;

class WebhookEventData
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $eventType,
        public readonly ?string $providerPaymentId,
        public readonly bool $success,
        /** @var array<string, mixed> */
        public readonly array $rawPayload = [],
    ) {}
}
