<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Refunds\RequestRefundAction;
use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RequestRefundRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\RefundResource;
use App\Models\Payment;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can(PermissionName::PaymentsView->value), 403);

        $payments = Payment::query()
            ->where('organization_id', $request->user()->organization_id)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(PaymentResource::collection($payments), 'Payments loaded.');
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        abort_unless($request->user()->can(PermissionName::PaymentsView->value), 403);

        return ApiResponse::success(new PaymentResource($payment), 'Payment loaded.');
    }

    public function refund(
        RequestRefundRequest $request,
        Payment $payment,
        RequestRefundAction $action
    ): JsonResponse {
        $this->authorize('refund', $payment);

        $refund = $action->handle(
            $payment,
            $request->user(),
            $request->validated('amount_minor'),
            $request->validated('reason'),
        );

        return ApiResponse::success(new RefundResource($refund), 'Refund processed.', status: 201);
    }
}
