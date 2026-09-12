<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Services\Courses\CourseRatingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function __construct(private readonly CourseRatingService $ratingService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Review::class);

        $reviews = Review::query()
            ->whereHas('course', fn ($query) => $query->where('organization_id', $request->user()->organization_id))
            ->with(['course', 'student'])
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->integer('course_id'), fn ($query, $courseId) => $query->where('course_id', $courseId))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(ReviewResource::collection($reviews), 'Reviews loaded.');
    }

    public function moderate(Request $request, Review $review): JsonResponse
    {
        $this->authorize('moderate', $review);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ReviewStatus::class)],
        ]);

        $review->update($validated);

        $this->ratingService->recalculate($review->course);

        return ApiResponse::success(new ReviewResource($review), 'Review status updated.');
    }
}
