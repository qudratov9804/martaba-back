<?php

namespace App\Http\Requests\Admin;

use App\Enums\CouponScopeType;
use App\Enums\CouponStatus;
use App\Enums\CouponType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return [
            'code' => [
                'sometimes', 'required', 'string', 'max:64', 'alpha_dash',
                Rule::unique('coupons', 'code')->where('organization_id', $this->user()->organization_id)->ignore($coupon),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::enum(CouponType::class)],
            'value' => ['sometimes', 'required', 'integer', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
            'minimum_order_minor' => ['nullable', 'integer', 'min:0'],
            'maximum_discount_minor' => ['nullable', 'integer', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['nullable', Rule::enum(CouponStatus::class)],
            'scope_type' => ['sometimes', 'required', Rule::enum(CouponScopeType::class)],
            'scope_id' => ['nullable', 'integer'],
        ];
    }
}
