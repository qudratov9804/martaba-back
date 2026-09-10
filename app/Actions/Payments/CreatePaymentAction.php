<?php

namespace App\Actions\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentTransactionType;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePaymentAction
{
    public function __construct(private readonly PaymentGatewayManager $gatewayManager) {}

    public function handle(Order $order, PaymentProvider $provider): Payment
    {
        if (! in_array($order->status, [OrderStatus::Pending, OrderStatus::Processing], true)) {
            throw ValidationException::withMessages([
                'order' => ['This order is not payable in its current state.'],
            ]);
        }

        return DB::transaction(function () use ($order, $provider) {
            $gateway = $this->gatewayManager->gateway($provider);
            $payment = $gateway->createPayment($order);

            $payment->transactions()->create([
                'type' => PaymentTransactionType::Payment,
                'status' => $payment->status->value,
                'amount_minor' => $payment->amount_minor,
                'currency' => $payment->currency,
                'created_at' => now(),
            ]);

            $order->update(['status' => OrderStatus::Processing]);

            return $payment;
        });
    }
}
