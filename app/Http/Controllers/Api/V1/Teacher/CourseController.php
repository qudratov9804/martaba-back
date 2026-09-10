<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Actions\Courses\ArchiveCourseAction;
use App\Actions\Courses\CreateCourseAction;
use App\Actions\Courses\PublishCourseAction;
use App\Actions\Courses\UnpublishCourseAction;
use App\Actions\Courses\UpdateCourseAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreCourseRequest;
use App\Http\Requests\Teacher\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\Audit\AuditLogger;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Course::class);

        $courses = Course::query()
            ->where(function ($query) use ($request) {
                $query->where('created_by', $request->user()->id)
                    ->orWhereHas('courseInstructors', fn ($q) => $q->where('user_id', $request->user()->id));
            })
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(CourseResource::collection($courses), 'Courses loaded.');
    }

    public function store(StoreCourseRequest $request, CreateCourseAction $action): JsonResponse
    {
        $this->authorize('create', Course::class);

        $course = $action->handle($request->user(), $request->validated());

        return ApiResponse::success(new CourseResource($course), 'Course created.', status: 201);
    }

    public function show(Course $course): JsonResponse
    {
        $this->authorize('view', $course);

        return ApiResponse::success(new CourseResource($course->load('sections.lessons')), 'Course loaded.');
    }

    public function update(UpdateCourseRequest $request, Course $course, UpdateCourseAction $action): JsonResponse
    {
        $this->authorize('update', $course);

        $course = $action->handle($course, $request->validated());

        return ApiResponse::success(new CourseResource($course), 'Course updated.');
    }

    public function destroy(Request $request, Course $course, AuditLogger $auditLogger): JsonResponse
    {
        $this->authorize('delete', $course);

        $auditLogger->record($request->user(), 'course.deleted', $course, ['title' => $course->title]);

        $course->delete();

        return ApiResponse::success(null, 'Course deleted.');
    }

    public function publish(Request $request, Course $course, PublishCourseAction $action): JsonResponse
    {
        $this->authorize('publish', $course);

        $course = $action->handle($course, $request->user());

        return ApiResponse::success(new CourseResource($course), 'Course published.');
    }

    public function unpublish(Course $course, UnpublishCourseAction $action): JsonResponse
    {
        $this->authorize('publish', $course);

        $course = $action->handle($course);

        return ApiResponse::success(new CourseResource($course), 'Course unpublished.');
    }

    public function archive(Course $course, ArchiveCourseAction $action): JsonResponse
    {
        $this->authorize('archive', $course);

        $course = $action->handle($course);

        return ApiResponse::success(new CourseResource($course), 'Course archived.');
    }
}
