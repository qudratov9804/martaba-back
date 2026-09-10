<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Actions\Checkout\CreateOrderAction;
use App\Actions\Checkout\PreviewCheckoutAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutPreviewRequest;
use App\Http\Resources\OrderResource;
use App\Models\Course;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function preview(CheckoutPreviewRequest $request, PreviewCheckoutAction $action): JsonResponse
    {
        $course = Course::findOrFail($request->validated('course_id'));

        $breakdown = $action->handle($request->user(), $course, $request->validated('coupon_code'));

        return ApiResponse::success([
            'course_id' => $course->id,
            'subtotal_minor' => $breakdown['subtotal_minor'],
            'discount_minor' => $breakdown['discount_minor'],
            'total_minor' => $breakdown['total_minor'],
            'currency' => $breakdown['currency'],
            'coupon_code' => $breakdown['coupon']?->code,
        ], 'Checkout preview computed.');
    }

    public function store(CheckoutPreviewRequest $request, CreateOrderAction $action): JsonResponse
    {
        $course = Course::findOrFail($request->validated('course_id'));

        $order = $action->handle($request->user(), $course, $request->validated('coupon_code'));

        return ApiResponse::success(new OrderResource($order->load('items')), 'Order created.', status: 201);
    }
}
