<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::CouponsView->value);
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return $user->can(PermissionName::CouponsView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::CouponsCreate->value);
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $user->can(PermissionName::CouponsUpdate->value);
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $user->can(PermissionName::CouponsDelete->value);
    }
}
