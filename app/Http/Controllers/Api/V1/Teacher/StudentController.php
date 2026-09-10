<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Models\Course;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request, Course $course): JsonResponse
    {
        $this->authorize('view', $course);

        $enrollments = $course->enrollments()
            ->with('student')
            ->orderByDesc('enrolled_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(EnrollmentResource::collection($enrollments), 'Students loaded.');
    }
}
