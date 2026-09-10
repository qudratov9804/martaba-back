<?php

namespace Database\Factories;

use App\Enums\CouponScopeType;
use App\Enums\CouponStatus;
use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'code' => strtoupper(fake()->unique()->bothify('SAVE####')),
            'name' => fake()->words(2, true),
            'type' => CouponType::Percentage,
            'value' => 10,
            'status' => CouponStatus::Active,
            'scope_type' => CouponScopeType::All,
        ];
    }
}
