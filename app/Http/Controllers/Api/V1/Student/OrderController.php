<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(OrderResource::collection($orders), 'Orders loaded.');
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return ApiResponse::success(new OrderResource($order->load('items')), 'Order loaded.');
    }
}
