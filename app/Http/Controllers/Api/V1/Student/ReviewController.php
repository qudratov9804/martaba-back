<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreReviewRequest;
use App\Http\Requests\Student\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Course;
use App\Models\Review;
use App\Services\Courses\CourseRatingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function __construct(private readonly CourseRatingService $ratingService) {}

    public function store(StoreReviewRequest $request, Course $course): JsonResponse
    {
        $user = $request->user();

        if (! $course->reviews_enabled) {
            throw ValidationException::withMessages([
                'course' => 'Reviews are disabled for this course.',
            ]);
        }

        $isEnrolled = $course->enrollments()
            ->where('student_id', $user->id)
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->exists();

        if (! $isEnrolled) {
            throw ValidationException::withMessages([
                'course' => 'You must be enrolled in this course to leave a review.',
            ]);
        }

        if ($course->reviews()->where('student_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'course' => 'You have already reviewed this course.',
            ]);
        }

        $review = $course->reviews()->create([
            'student_id' => $user->id,
            ...$request->validated(),
        ]);

        $this->ratingService->recalculate($course);

        return ApiResponse::success(new ReviewResource($review), 'Review submitted.', status: 201);
    }

    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        $this->ratingService->recalculate($review->course);

        return ApiResponse::success(new ReviewResource($review), 'Review updated.');
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $course = $review->course;
        $review->delete();

        $this->ratingService->recalculate($course);

        return ApiResponse::success(null, 'Review deleted.');
    }
}
