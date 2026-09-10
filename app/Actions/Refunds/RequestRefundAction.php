<?php

namespace App\Actions\Refunds;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionType;
use App\Enums\RefundStatus;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestRefundAction
{
    public function __construct(
        private readonly PaymentGatewayManager $gatewayManager,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function handle(Payment $payment, User $requester, int $amountMinor, ?string $reason = null): Refund
    {
        if ($payment->status !== PaymentStatus::Succeeded) {
            throw ValidationException::withMessages([
                'payment' => ['Only a succeeded payment can be refunded.'],
            ]);
        }

        $alreadyRefunded = (int) $payment->refunds()->where('status', RefundStatus::Succeeded)->sum('amount_minor');

        if ($amountMinor > $payment->amount_minor - $alreadyRefunded) {
            throw ValidationException::withMessages([
                'amount_minor' => ['The refund amount exceeds the remaining refundable balance.'],
            ]);
        }

        return DB::transaction(function () use ($payment, $requester, $amountMinor, $reason, $alreadyRefunded) {
            $refund = Refund::create([
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'amount_minor' => $amountMinor,
                'reason' => $reason,
                'status' => RefundStatus::Requested,
                'requested_by' => $requester->id,
            ]);

            $gateway = $this->gatewayManager->gateway($payment->provider);
            $result = $gateway->refund($payment, $amountMinor, $reason);

            if (! $result['succeeded']) {
                $refund->update(['status' => RefundStatus::Failed]);

                return $refund;
            }

            $refund->update([
                'status' => RefundStatus::Succeeded,
                'provider_refund_id' => $result['provider_refund_id'],
                'processed_by' => $requester->id,
                'processed_at' => now(),
            ]);

            $totalRefunded = $alreadyRefunded + $amountMinor;
            $isFullyRefunded = $totalRefunded >= $payment->amount_minor;

            $payment->update([
                'status' => $isFullyRefunded ? PaymentStatus::Refunded : PaymentStatus::PartiallyRefunded,
            ]);

            $payment->order->update([
                'status' => $isFullyRefunded ? OrderStatus::Refunded : OrderStatus::PartiallyRefunded,
            ]);

            $payment->transactions()->create([
                'type' => $isFullyRefunded ? PaymentTransactionType::Refund : PaymentTransactionType::PartialRefund,
                'status' => RefundStatus::Succeeded->value,
                'amount_minor' => $amountMinor,
                'currency' => $payment->currency,
                'provider_transaction_id' => $result['provider_refund_id'],
                'processed_at' => now(),
                'created_at' => now(),
            ]);

            $this->auditLogger->record(
                $requester,
                'refund.processed',
                $refund,
                [],
                ['amount_minor' => $amountMinor, 'payment_id' => $payment->id],
            );

            return $refund;
        });
    }
}
