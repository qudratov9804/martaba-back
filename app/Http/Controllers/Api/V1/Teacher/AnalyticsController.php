<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Analytics\TeacherAnalyticsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function overview(Request $request, TeacherAnalyticsService $service): JsonResponse
    {
        abort_unless($request->user()->can(PermissionName::AnalyticsView->value), 403);

        return ApiResponse::success($service->overview($request->user()), 'Analytics overview loaded.');
    }

    public function revenue(Request $request, TeacherAnalyticsService $service): JsonResponse
    {
        abort_unless($request->user()->can(PermissionName::AnalyticsView->value), 403);

        return ApiResponse::success($service->revenueByCourse($request->user()), 'Revenue breakdown loaded.');
    }

    public function course(Course $course, TeacherAnalyticsService $service): JsonResponse
    {
        $this->authorize('view', $course);

        return ApiResponse::success($service->courseOverview($course), 'Course analytics loaded.');
    }
}
