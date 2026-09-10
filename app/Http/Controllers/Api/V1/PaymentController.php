<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Payments\CreatePaymentAction;
use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\CreatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function store(CreatePaymentRequest $request, Order $order, CreatePaymentAction $action): JsonResponse
    {
        if ($order->student_id !== $request->user()->id) {
            throw new AuthorizationException('This is not your order.');
        }

        $payment = $action->handle($order, PaymentProvider::from($request->validated('provider')));

        return ApiResponse::success(new PaymentResource($payment), 'Payment created.', status: 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        return ApiResponse::success(new PaymentResource($payment), 'Payment loaded.');
    }
}
