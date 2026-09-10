<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Actions\Learning\CompleteLessonAction;
use App\Actions\Learning\StartLessonAction;
use App\Actions\Learning\UpdateLessonProgressAction;
use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateLessonProgressRequest;
use App\Http\Resources\CourseLessonResource;
use App\Http\Resources\CourseProgressResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\LessonProgressResource;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseProgress;
use App\Models\LessonProgress;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    public function learn(Request $request, Course $course): JsonResponse
    {
        $this->ensureEnrolled($request->user(), $course);

        $course->load(['sections' => fn ($query) => $query->where('is_published', true)->with([
            'lessons' => fn ($query) => $query->where('is_published', true),
        ])]);

        $progress = CourseProgress::query()
            ->where('student_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        $lessonProgress = LessonProgress::query()
            ->where('student_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->get();

        return ApiResponse::success([
            'course' => new CourseResource($course),
            'progress' => $progress ? new CourseProgressResource($progress) : null,
            'lesson_progress' => LessonProgressResource::collection($lessonProgress),
        ], 'Course learning data loaded.');
    }

    public function showLesson(Request $request, CourseLesson $lesson): JsonResponse
    {
        $this->ensureLessonAccess($request->user(), $lesson);

        return ApiResponse::success(new CourseLessonResource($lesson->load('contents')), 'Lesson loaded.');
    }

    public function start(Request $request, CourseLesson $lesson, StartLessonAction $action): JsonResponse
    {
        $this->ensureLessonAccess($request->user(), $lesson);

        $progress = $action->handle($request->user(), $lesson);

        return ApiResponse::success(new LessonProgressResource($progress), 'Lesson started.');
    }

    public function updateProgress(
        UpdateLessonProgressRequest $request,
        CourseLesson $lesson,
        UpdateLessonProgressAction $action
    ): JsonResponse {
        $this->ensureLessonAccess($request->user(), $lesson);

        $progress = $action->handle($request->user(), $lesson, $request->validated());

        return ApiResponse::success(new LessonProgressResource($progress), 'Progress updated.');
    }

    public function complete(Request $request, CourseLesson $lesson, CompleteLessonAction $action): JsonResponse
    {
        $this->ensureLessonAccess($request->user(), $lesson);

        $progress = $action->handle($request->user(), $lesson);

        return ApiResponse::success(new LessonProgressResource($progress), 'Lesson completed.');
    }

    private function ensureEnrolled(User $user, Course $course): void
    {
        $isEnrolled = $course->enrollments()
            ->where('student_id', $user->id)
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->exists();

        if (! $isEnrolled && ! $course->isOwnedBy($user)) {
            throw new AuthorizationException('You are not enrolled in this course.');
        }
    }

    private function ensureLessonAccess(User $user, CourseLesson $lesson): void
    {
        if ($lesson->is_preview) {
            return;
        }

        $this->ensureEnrolled($user, $lesson->course);
    }
}
