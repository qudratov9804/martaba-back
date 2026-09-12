<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\CourseStatus;
use App\Enums\CourseVisibility;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Http\Resources\ReviewResource;
use App\Models\Course;
use App\Models\Organization;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Course::query()
            ->where('status', CourseStatus::Published)
            ->where('visibility', CourseVisibility::Public)
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($request->integer('category_id'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($request->string('level')->toString(), fn ($query, $level) => $query->where('level', $level))
            ->when($request->string('pricing_type')->toString(), fn ($query, $type) => $query->where('pricing_type', $type));

        if ($organizationSlug = $request->string('organization')->toString()) {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
            $query->where('organization_id', $organization->id);
        }

        $courses = $query->orderByDesc('published_at')->paginate($request->integer('per_page', 20));

        return ApiResponse::success(CourseResource::collection($courses), 'Courses loaded.');
    }

    public function show(string $slug, Request $request): JsonResponse
    {
        $query = Course::query()
            ->where('slug', $slug)
            ->where('status', CourseStatus::Published)
            ->where('visibility', CourseVisibility::Public);

        if ($organizationSlug = $request->string('organization')->toString()) {
            $organization = Organization::where('slug', $organizationSlug)->firstOrFail();
            $query->where('organization_id', $organization->id);
        }

        $course = $query->with(['sections' => fn ($q) => $q->where('is_published', true)->with([
            'lessons' => fn ($q) => $q->where('is_published', true),
        ])])->firstOrFail();

        return ApiResponse::success(new CourseResource($course), 'Course loaded.');
    }

    public function reviews(string $slug, Request $request): JsonResponse
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('status', CourseStatus::Published)
            ->where('visibility', CourseVisibility::Public)
            ->firstOrFail();

        $reviews = $course->reviews()
            ->where('status', ReviewStatus::Published)
            ->with('student')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(ReviewResource::collection($reviews), 'Reviews loaded.');
    }
}
