<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Categories\CreateCategoryAction;
use App\Actions\Categories\UpdateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::query()
            ->where('organization_id', $request->user()->organization_id)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return ApiResponse::success(CategoryResource::collection($categories), 'Categories loaded.');
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $action): JsonResponse
    {
        $this->authorize('create', Category::class);

        $category = $action->handle($request->user()->organization_id, $request->validated());

        return ApiResponse::success(new CategoryResource($category), 'Category created.', status: 201);
    }

    public function show(Category $category): JsonResponse
    {
        $this->authorize('view', $category);

        return ApiResponse::success(new CategoryResource($category->load('children')), 'Category loaded.');
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategoryAction $action): JsonResponse
    {
        $this->authorize('update', $category);

        $category = $action->handle($category, $request->validated());

        return ApiResponse::success(new CategoryResource($category), 'Category updated.');
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return ApiResponse::success(null, 'Category deleted.');
    }
}
