<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can(PermissionName::OrdersView->value), 403);

        $orders = Order::query()
            ->where('organization_id', $request->user()->organization_id)
            ->with('student:id,name,email')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
        ]);
    }
}
