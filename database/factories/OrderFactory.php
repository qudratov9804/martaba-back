<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'student_id' => User::factory(),
            'number' => 'ORD-'.now()->format('Y').'-'.Str::upper(Str::random(8)),
            'status' => OrderStatus::Pending,
            'subtotal_minor' => 5000,
            'discount_minor' => 0,
            'total_minor' => 5000,
            'currency' => 'USD',
        ];
    }
}
