<?php

namespace App\Services\Payments;

use App\Enums\CouponScopeType;
use App\Enums\CouponStatus;
use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Course;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponValidationService
{
    /**
     * Validate a coupon against a specific course/user/order-total. Must be
     * called inside a transaction with the coupon row locked (lockForUpdate)
     * so concurrent checkouts cannot both slip past the usage limits.
     */
    public function validate(Coupon $coupon, User $student, Course $course, int $subtotalMinor): void
    {
        if ($coupon->status !== CouponStatus::Active) {
            $this->fail('This coupon is not active.');
        }

        $now = now();

        if ($coupon->starts_at && $now->lessThan($coupon->starts_at)) {
            $this->fail('This coupon is not valid yet.');
        }

        if ($coupon->ends_at && $now->greaterThan($coupon->ends_at)) {
            $this->fail('This coupon has expired.');
        }

        if (! $this->matchesScope($coupon, $course)) {
            $this->fail('This coupon does not apply to this course.');
        }

        if ($coupon->minimum_order_minor !== null && $subtotalMinor < $coupon->minimum_order_minor) {
            $this->fail('This order does not meet the minimum amount required for this coupon.');
        }

        if ($coupon->usage_limit !== null && $coupon->usages()->count() >= $coupon->usage_limit) {
            $this->fail('This coupon has reached its usage limit.');
        }

        if ($coupon->usage_limit_per_user !== null) {
            $userUsages = CouponUsage::where('coupon_id', $coupon->id)->where('student_id', $student->id)->count();

            if ($userUsages >= $coupon->usage_limit_per_user) {
                $this->fail('You have already used this coupon the maximum number of times.');
            }
        }
    }

    public function calculateDiscount(Coupon $coupon, int $subtotalMinor): int
    {
        $discount = $coupon->type === CouponType::Percentage
            ? (int) floor($subtotalMinor * $coupon->value / 100)
            : (int) $coupon->value;

        if ($coupon->maximum_discount_minor !== null) {
            $discount = min($discount, $coupon->maximum_discount_minor);
        }

        return min($discount, $subtotalMinor);
    }

    private function matchesScope(Coupon $coupon, Course $course): bool
    {
        return match ($coupon->scope_type) {
            CouponScopeType::All => true,
            CouponScopeType::Course => $coupon->scope_id === $course->id,
            CouponScopeType::Category => $coupon->scope_id !== null && $coupon->scope_id === $course->category_id,
        };
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['coupon_code' => [$message]]);
    }
}
