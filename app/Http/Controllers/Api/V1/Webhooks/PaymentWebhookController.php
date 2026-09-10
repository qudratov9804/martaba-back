<?php

namespace App\Http\Controllers\Api\V1\Webhooks;

use App\Actions\Payments\ProcessPaymentWebhookAction;
use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $provider, ProcessPaymentWebhookAction $action): JsonResponse
    {
        $paymentProvider = PaymentProvider::tryFrom($provider);

        if (! $paymentProvider) {
            throw ValidationException::withMessages(['provider' => ['Unknown payment provider.']]);
        }

        $webhook = $action->handle($paymentProvider, $request->all(), $request->header('X-Webhook-Signature'));

        return ApiResponse::success(['status' => $webhook->status], 'Webhook processed.');
    }
}
