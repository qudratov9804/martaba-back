<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\PermissionName;
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
        abort_unless($request->user()->can(PermissionName::OrdersView->value), 403);

        $orders = Order::query()
            ->where('organization_id', $request->user()->organization_id)
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(OrderResource::collection($orders), 'Orders loaded.');
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user()->can(PermissionName::OrdersView->value), 403);

        return ApiResponse::success(new OrderResource($order->load('items')), 'Order loaded.');
    }
}
