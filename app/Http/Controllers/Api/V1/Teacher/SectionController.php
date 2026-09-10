<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Actions\Courses\ReorderSectionsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\ReorderSectionsRequest;
use App\Http\Requests\Teacher\StoreSectionRequest;
use App\Http\Requests\Teacher\UpdateSectionRequest;
use App\Http\Resources\CourseSectionResource;
use App\Models\Course;
use App\Models\CourseSection;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class SectionController extends Controller
{
    public function store(StoreSectionRequest $request, Course $course): JsonResponse
    {
        $this->authorize('manageContent', $course);

        $section = $course->sections()->create($request->validated());

        return ApiResponse::success(new CourseSectionResource($section), 'Section created.', status: 201);
    }

    public function update(UpdateSectionRequest $request, CourseSection $section): JsonResponse
    {
        $this->authorize('manageContent', $section->course);

        $section->update($request->validated());

        return ApiResponse::success(new CourseSectionResource($section), 'Section updated.');
    }

    public function destroy(CourseSection $section): JsonResponse
    {
        $this->authorize('manageContent', $section->course);

        $section->delete();

        return ApiResponse::success(null, 'Section deleted.');
    }

    public function reorder(ReorderSectionsRequest $request, Course $course, ReorderSectionsAction $action): JsonResponse
    {
        $this->authorize('manageContent', $course);

        $action->handle($course, $request->validated('section_ids'));

        return ApiResponse::success(CourseSectionResource::collection($course->sections()->get()), 'Sections reordered.');
    }
}
