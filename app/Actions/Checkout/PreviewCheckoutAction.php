<?php

namespace App\Actions\Checkout;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\User;

class PreviewCheckoutAction
{
    public function __construct(private readonly BuildOrderBreakdownAction $buildOrderBreakdownAction) {}

    /**
     * @return array{course: Course, coupon: ?Coupon, subtotal_minor: int, discount_minor: int, total_minor: int, currency: string}
     */
    public function handle(User $student, Course $course, ?string $couponCode): array
    {
        return $this->buildOrderBreakdownAction->handle($student, $course, $couponCode, lockCoupon: false);
    }
}
