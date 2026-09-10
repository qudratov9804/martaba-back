<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreContentRequest;
use App\Http\Resources\CourseContentResource;
use App\Models\CourseContent;
use App\Models\CourseLesson;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ContentController extends Controller
{
    public function store(StoreContentRequest $request, CourseLesson $lesson): JsonResponse
    {
        $this->authorize('manageContent', $lesson->course);

        $content = $lesson->contents()->create($request->validated());

        return ApiResponse::success(new CourseContentResource($content), 'Content block added.', status: 201);
    }

    public function update(StoreContentRequest $request, CourseContent $content): JsonResponse
    {
        $this->authorize('manageContent', $content->lesson->course);

        $content->update($request->validated());

        return ApiResponse::success(new CourseContentResource($content), 'Content block updated.');
    }

    public function destroy(CourseContent $content): JsonResponse
    {
        $this->authorize('manageContent', $content->lesson->course);

        $content->delete();

        return ApiResponse::success(null, 'Content block deleted.');
    }
}
