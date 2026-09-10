<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Actions\Courses\ReorderLessonsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\ReorderLessonsRequest;
use App\Http\Requests\Teacher\StoreLessonRequest;
use App\Http\Requests\Teacher\UpdateLessonRequest;
use App\Http\Resources\CourseLessonResource;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class LessonController extends Controller
{
    public function store(StoreLessonRequest $request, CourseSection $section): JsonResponse
    {
        $this->authorize('manageContent', $section->course);

        $lesson = $section->lessons()->create([
            ...$request->validated(),
            'course_id' => $section->course_id,
        ]);

        return ApiResponse::success(new CourseLessonResource($lesson), 'Lesson created.', status: 201);
    }

    public function update(UpdateLessonRequest $request, CourseLesson $lesson): JsonResponse
    {
        $this->authorize('manageContent', $lesson->course);

        $lesson->update($request->validated());

        return ApiResponse::success(new CourseLessonResource($lesson), 'Lesson updated.');
    }

    public function destroy(CourseLesson $lesson): JsonResponse
    {
        $this->authorize('manageContent', $lesson->course);

        $lesson->delete();

        return ApiResponse::success(null, 'Lesson deleted.');
    }

    public function reorder(ReorderLessonsRequest $request, CourseSection $section, ReorderLessonsAction $action): JsonResponse
    {
        $this->authorize('manageContent', $section->course);

        $action->handle($section, $request->validated('lesson_ids'));

        return ApiResponse::success(CourseLessonResource::collection($section->lessons()->get()), 'Lessons reordered.');
    }
}
