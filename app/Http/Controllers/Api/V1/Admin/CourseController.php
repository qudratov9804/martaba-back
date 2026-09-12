<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Courses\ArchiveCourseAction;
use App\Actions\Courses\PublishCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Course::class);

        $courses = Course::query()
            ->where('organization_id', $request->user()->organization_id)
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(CourseResource::collection($courses), 'Courses loaded.');
    }

    public function show(Course $course): JsonResponse
    {
        $this->authorize('view', $course);

        return ApiResponse::success(new CourseResource($course->load('sections.lessons')), 'Course loaded.');
    }

    public function publish(Request $request, Course $course, PublishCourseAction $action): JsonResponse
    {
        $this->authorize('publish', $course);

        $course = $action->handle($course, $request->user());

        return ApiResponse::success(new CourseResource($course), 'Course published.');
    }

    public function archive(Course $course, ArchiveCourseAction $action): JsonResponse
    {
        $this->authorize('archive', $course);

        $course = $action->handle($course);

        return ApiResponse::success(new CourseResource($course), 'Course archived.');
    }
}
