<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $payment->student_id === $user->id || $user->can(PermissionName::PaymentsView->value);
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $user->can(PermissionName::PaymentsRefund->value);
    }
}
