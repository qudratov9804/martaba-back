<?php

namespace App\Actions\Checkout;

use App\Enums\CourseStatus;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\User;
use App\Services\Payments\CouponValidationService;
use Illuminate\Validation\ValidationException;

class BuildOrderBreakdownAction
{
    public function __construct(private readonly CouponValidationService $couponValidationService) {}

    /**
     * @return array{course: Course, coupon: ?Coupon, subtotal_minor: int, discount_minor: int, total_minor: int, currency: string}
     */
    public function handle(User $student, Course $course, ?string $couponCode, bool $lockCoupon = false): array
    {
        if ($course->status !== CourseStatus::Published) {
            throw ValidationException::withMessages(['course' => ['This course is not available for purchase.']]);
        }

        $subtotalMinor = $course->discount_price_minor ?? $course->price_minor;
        $discountMinor = 0;
        $coupon = null;

        if ($couponCode) {
            $query = Coupon::query()
                ->where('organization_id', $course->organization_id)
                ->where('code', $couponCode);

            $coupon = $lockCoupon ? $query->lockForUpdate()->first() : $query->first();

            if (! $coupon) {
                throw ValidationException::withMessages(['coupon_code' => ['This coupon code is invalid.']]);
            }

            $this->couponValidationService->validate($coupon, $student, $course, $subtotalMinor);
            $discountMinor = $this->couponValidationService->calculateDiscount($coupon, $subtotalMinor);
        }

        return [
            'course' => $course,
            'coupon' => $coupon,
            'subtotal_minor' => $subtotalMinor,
            'discount_minor' => $discountMinor,
            'total_minor' => max(0, $subtotalMinor - $discountMinor),
            'currency' => $course->currency,
        ];
    }
}
