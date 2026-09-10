<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Coupon::class);

        $coupons = Coupon::query()
            ->where('organization_id', $request->user()->organization_id)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(CouponResource::collection($coupons), 'Coupons loaded.');
    }

    public function store(StoreCouponRequest $request): JsonResponse
    {
        $this->authorize('create', Coupon::class);

        $coupon = Coupon::create([
            ...$request->validated(),
            'organization_id' => $request->user()->organization_id,
        ]);

        return ApiResponse::success(new CouponResource($coupon), 'Coupon created.', status: 201);
    }

    public function show(Coupon $coupon): JsonResponse
    {
        $this->authorize('view', $coupon);

        return ApiResponse::success(new CouponResource($coupon), 'Coupon loaded.');
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): JsonResponse
    {
        $this->authorize('update', $coupon);

        $coupon->update($request->validated());

        return ApiResponse::success(new CouponResource($coupon), 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        $this->authorize('delete', $coupon);

        $coupon->delete();

        return ApiResponse::success(null, 'Coupon deleted.');
    }
}
