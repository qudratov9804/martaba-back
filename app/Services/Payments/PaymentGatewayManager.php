<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentProvider;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function __construct(private readonly Container $container) {}

    public function gateway(PaymentProvider $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            PaymentProvider::Manual => $this->container->make(ManualPaymentGateway::class),
            PaymentProvider::Click => $this->container->make(ClickPaymentGateway::class),
            default => throw new InvalidArgumentException(
                "No payment gateway adapter is registered for provider [{$provider->value}] yet."
            ),
        };
    }
}
