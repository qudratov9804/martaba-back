<?php

namespace App\Actions\Checkout;

use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    public function __construct(private readonly BuildOrderBreakdownAction $buildOrderBreakdownAction) {}

    public function handle(User $student, Course $course, ?string $couponCode = null): Order
    {
        return DB::transaction(function () use ($student, $course, $couponCode) {
            $alreadyEnrolled = $course->enrollments()
                ->where('student_id', $student->id)
                ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
                ->exists();

            if ($alreadyEnrolled) {
                throw ValidationException::withMessages(['course' => ['You are already enrolled in this course.']]);
            }

            $breakdown = $this->buildOrderBreakdownAction->handle($student, $course, $couponCode, lockCoupon: true);

            $order = Order::create([
                'organization_id' => $course->organization_id,
                'student_id' => $student->id,
                'number' => $this->generateOrderNumber(),
                'status' => OrderStatus::Pending,
                'subtotal_minor' => $breakdown['subtotal_minor'],
                'discount_minor' => $breakdown['discount_minor'],
                'total_minor' => $breakdown['total_minor'],
                'currency' => $breakdown['currency'],
                'coupon_id' => $breakdown['coupon']?->id,
                'billing_name' => $student->name,
                'billing_email' => $student->email,
            ]);

            $order->items()->create([
                'course_id' => $course->id,
                'title_snapshot' => $course->title,
                'price_minor' => $breakdown['subtotal_minor'],
                'discount_minor' => $breakdown['discount_minor'],
                'total_minor' => $breakdown['total_minor'],
                'currency' => $breakdown['currency'],
                'created_at' => now(),
            ]);

            if ($breakdown['coupon']) {
                $breakdown['coupon']->usages()->create([
                    'student_id' => $student->id,
                    'order_id' => $order->id,
                    'discount_minor' => $breakdown['discount_minor'],
                    'used_at' => now(),
                ]);
            }

            return $order;
        });
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('Y').'-'.Str::padLeft((string) (Order::count() + 1), 6, '0').'-'.Str::upper(Str::random(4));
    }
}
