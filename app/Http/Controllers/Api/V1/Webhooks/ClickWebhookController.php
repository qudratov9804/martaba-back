<?php

namespace App\Http\Controllers\Api\V1\Webhooks;

use App\Actions\Payments\FinalizePaymentAction;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\WebhookStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Click (click.uz) calls the merchant back twice per transaction, Prepare
 * (action=0) then Complete (action=1), and always expects HTTP 200 with
 * its own {click_trans_id, merchant_trans_id, error, error_note, ...}
 * body — never the application's standard success/error envelope, and
 * never a 4xx/5xx status even when the transaction is rejected. That is
 * why this bypasses PaymentWebhookController/ProcessPaymentWebhookAction,
 * which assume a single generic callback per provider.
 */
class ClickWebhookController extends Controller
{
    private const ERROR_SIGN_FAILED = -1;

    private const ERROR_AMOUNT_MISMATCH = -2;

    private const ERROR_ALREADY_PAID = -4;

    private const ERROR_ORDER_NOT_FOUND = -5;

    private const ERROR_TRANSACTION_NOT_FOUND = -6;

    public function handle(Request $request, FinalizePaymentAction $finalizePaymentAction): JsonResponse
    {
        $data = $request->all();
        $action = (int) ($data['action'] ?? -1);

        return $action === 1
            ? $this->complete($data, $finalizePaymentAction)
            : $this->prepare($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function prepare(array $data): JsonResponse
    {
        $payment = Payment::where('id', $data['merchant_trans_id'] ?? null)
            ->where('provider', PaymentProvider::Click->value)
            ->first();

        if (! $payment) {
            return $this->response($data, self::ERROR_ORDER_NOT_FOUND, 'Order not found.');
        }

        if (! $this->signatureValid($data, includesPrepareId: false)) {
            return $this->response($data, self::ERROR_SIGN_FAILED, 'SIGN CHECK FAILED.');
        }

        if (! $this->amountMatches($payment, $data)) {
            return $this->response($data, self::ERROR_AMOUNT_MISMATCH, 'Incorrect parameter amount.');
        }

        if ($payment->status === PaymentStatus::Succeeded) {
            return $this->response($data, self::ERROR_ALREADY_PAID, 'Already paid.');
        }

        $this->recordWebhook('prepare', $data, $payment);

        $payment->update([
            'provider_payment_id' => (string) $data['click_trans_id'],
            'provider_transaction_id' => (string) $data['click_trans_id'],
        ]);

        return $this->response($data, 0, 'Success.', ['merchant_prepare_id' => $payment->id]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function complete(array $data, FinalizePaymentAction $finalizePaymentAction): JsonResponse
    {
        // lockForUpdate() only holds a row lock inside an active transaction,
        // so the whole read-decide-write sequence must run in one, or two
        // concurrent Complete calls for the same transaction could both pass
        // the "already succeeded" check and double-enroll the student.
        return DB::transaction(function () use ($data, $finalizePaymentAction) {
            $payment = Payment::where('id', $data['merchant_prepare_id'] ?? $data['merchant_trans_id'] ?? null)
                ->where('provider', PaymentProvider::Click->value)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return $this->response($data, self::ERROR_TRANSACTION_NOT_FOUND, 'Transaction does not exist.');
            }

            if (! $this->signatureValid($data, includesPrepareId: true)) {
                return $this->response($data, self::ERROR_SIGN_FAILED, 'SIGN CHECK FAILED.');
            }

            if ($payment->status === PaymentStatus::Succeeded) {
                return $this->response($data, self::ERROR_ALREADY_PAID, 'Already paid.', ['merchant_confirm_id' => $payment->id]);
            }

            if ((int) ($data['error'] ?? 0) < 0) {
                $this->recordWebhook('complete-cancelled', $data, $payment);
                $finalizePaymentAction->fail($payment, [
                    'failure_code' => (string) $data['error'],
                    'failure_message' => $data['error_note'] ?? 'Cancelled by Click.',
                ]);

                return $this->response($data, (int) $data['error'], 'Transaction cancelled.');
            }

            if (! $this->amountMatches($payment, $data)) {
                return $this->response($data, self::ERROR_AMOUNT_MISMATCH, 'Incorrect parameter amount.');
            }

            $this->recordWebhook('complete', $data, $payment);
            $finalizePaymentAction->succeed($payment);

            return $this->response($data, 0, 'Success.', ['merchant_confirm_id' => $payment->id]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function signatureValid(array $data, bool $includesPrepareId): bool
    {
        $parts = [
            $data['click_trans_id'] ?? '',
            $data['service_id'] ?? '',
            config('services.click.secret_key'),
            $data['merchant_trans_id'] ?? '',
        ];

        if ($includesPrepareId) {
            $parts[] = $data['merchant_prepare_id'] ?? '';
        }

        $parts[] = $data['amount'] ?? '';
        $parts[] = $data['action'] ?? '';
        $parts[] = $data['sign_time'] ?? '';

        $expected = md5(implode('', $parts));

        return hash_equals($expected, (string) ($data['sign_string'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function amountMatches(Payment $payment, array $data): bool
    {
        return abs((float) ($data['amount'] ?? 0) - (float) $payment->amount_minor) < 0.01;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function recordWebhook(string $stage, array $data, Payment $payment): void
    {
        PaymentWebhook::create([
            'provider' => PaymentProvider::Click->value,
            'event_id' => $stage.':'.($data['click_trans_id'] ?? $payment->id),
            'event_type' => $stage,
            'signature' => $data['sign_string'] ?? null,
            'payload_json' => $data,
            'status' => WebhookStatus::Processed,
            'processed_at' => now(),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $extra
     */
    private function response(array $data, int $error, string $errorNote, array $extra = []): JsonResponse
    {
        return response()->json([
            'click_trans_id' => $data['click_trans_id'] ?? null,
            'merchant_trans_id' => $data['merchant_trans_id'] ?? null,
            ...$extra,
            'error' => $error,
            'error_note' => $errorNote,
        ]);
    }
}
