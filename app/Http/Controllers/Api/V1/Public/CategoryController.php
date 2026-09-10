<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\CategoryStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Organization;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()
            ->where('status', CategoryStatus::Active)
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->where('status', CategoryStatus::Active)])
            ->orderBy('sort_order');

        if ($organizationSlug = $request->string('organization')->toString()) {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
            $query->where('organization_id', $organization->id);
        }

        return ApiResponse::success(CategoryResource::collection($query->get()), 'Categories loaded.');
    }

    public function show(string $slug, Request $request): JsonResponse
    {
        $query = Category::query()->where('slug', $slug)->where('status', CategoryStatus::Active);

        if ($organizationSlug = $request->string('organization')->toString()) {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
            $query->where('organization_id', $organization->id);
        }

        $category = $query->with('children')->firstOrFail();

        return ApiResponse::success(new CategoryResource($category), 'Category loaded.');
    }
}
