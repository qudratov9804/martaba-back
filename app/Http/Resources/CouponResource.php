<?php

namespace App\Http\Resources;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Coupon */
class CouponResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'currency' => $this->currency,
            'minimum_order_minor' => $this->minimum_order_minor,
            'maximum_discount_minor' => $this->maximum_discount_minor,
            'usage_limit' => $this->usage_limit,
            'usage_limit_per_user' => $this->usage_limit_per_user,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'status' => $this->status,
            'scope_type' => $this->scope_type,
            'scope_id' => $this->scope_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
