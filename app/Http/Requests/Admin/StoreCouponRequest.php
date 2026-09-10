<?php

namespace App\Http\Requests\Admin;

use App\Enums\CouponScopeType;
use App\Enums\CouponStatus;
use App\Enums\CouponType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
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
        return [
            'code' => [
                'required', 'string', 'max:64', 'alpha_dash',
                Rule::unique('coupons', 'code')->where('organization_id', $this->user()->organization_id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(CouponType::class)],
            'value' => [
                'required', 'integer', 'min:1',
                Rule::when($this->input('type') === CouponType::Percentage->value, ['max:100']),
            ],
            'currency' => ['nullable', 'string', 'size:3'],
            'minimum_order_minor' => ['nullable', 'integer', 'min:0'],
            'maximum_discount_minor' => ['nullable', 'integer', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['nullable', Rule::enum(CouponStatus::class)],
            'scope_type' => ['required', Rule::enum(CouponScopeType::class)],
            'scope_id' => ['nullable', 'integer', 'required_unless:scope_type,all'],
        ];
    }
}
