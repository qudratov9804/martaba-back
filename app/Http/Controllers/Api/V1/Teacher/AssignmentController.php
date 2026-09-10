<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreAssignmentRequest;
use App\Http\Requests\Teacher\UpdateAssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Course;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AssignmentController extends Controller
{
    public function index(Course $course): JsonResponse
    {
        $this->authorize('view', $course);

        return ApiResponse::success(AssignmentResource::collection($course->assignments), 'Assignments loaded.');
    }

    public function store(StoreAssignmentRequest $request, Course $course): JsonResponse
    {
        $this->authorize('manageContent', $course);

        $assignment = $course->assignments()->create($request->validated());

        return ApiResponse::success(new AssignmentResource($assignment), 'Assignment created.', status: 201);
    }

    public function show(Assignment $assignment): JsonResponse
    {
        $this->authorize('view', $assignment->course);

        return ApiResponse::success(new AssignmentResource($assignment), 'Assignment loaded.');
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment): JsonResponse
    {
        $this->authorize('manageContent', $assignment->course);

        $assignment->update($request->validated());

        return ApiResponse::success(new AssignmentResource($assignment), 'Assignment updated.');
    }

    public function destroy(Assignment $assignment): JsonResponse
    {
        $this->authorize('manageContent', $assignment->course);

        $assignment->delete();

        return ApiResponse::success(null, 'Assignment deleted.');
    }
}
