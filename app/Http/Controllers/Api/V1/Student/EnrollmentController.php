<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Actions\Enrollments\EnrollStudentAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $enrollments = $request->user()->enrollments()
            ->with('course')
            ->orderByDesc('enrolled_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(EnrollmentResource::collection($enrollments), 'Enrollments loaded.');
    }

    public function show(Request $request, Enrollment $enrollment): JsonResponse
    {
        $this->authorize('view', $enrollment);

        return ApiResponse::success(new EnrollmentResource($enrollment->load('course')), 'Enrollment loaded.');
    }

    public function store(Request $request, Course $course, EnrollStudentAction $action): JsonResponse
    {
        $enrollment = $action->handle($request->user(), $course);

        return ApiResponse::success(new EnrollmentResource($enrollment), 'Enrolled successfully.', status: 201);
    }
}
