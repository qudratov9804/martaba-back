<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can(PermissionName::PaymentsView->value), 403);

        $payments = Payment::query()
            ->where('organization_id', $request->user()->organization_id)
            ->with('student:id,name,email')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
        ]);
    }
}
