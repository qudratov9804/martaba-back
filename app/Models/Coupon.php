<?php

namespace App\Models;

use App\Enums\CouponScopeType;
use App\Enums\CouponStatus;
use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'organization_id', 'code', 'name', 'type', 'value', 'currency',
    'minimum_order_minor', 'maximum_discount_minor', 'usage_limit', 'usage_limit_per_user',
    'starts_at', 'ends_at', 'status', 'scope_type', 'scope_id', 'metadata_json',
])]
class Coupon extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'status' => CouponStatus::class,
            'scope_type' => CouponScopeType::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata_json' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return HasMany<CouponUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }
}
