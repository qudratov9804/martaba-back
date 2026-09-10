<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Actions\Assignments\GradeAssignmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\GradeAssignmentRequest;
use App\Http\Resources\AssignmentSubmissionResource;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentSubmissionController extends Controller
{
    public function index(Request $request, Assignment $assignment): JsonResponse
    {
        $this->authorize('view', $assignment->course);

        $submissions = $assignment->submissions()
            ->with(['student', 'media'])
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::success(AssignmentSubmissionResource::collection($submissions), 'Submissions loaded.');
    }

    public function grade(
        GradeAssignmentRequest $request,
        AssignmentSubmission $submission,
        GradeAssignmentAction $action
    ): JsonResponse {
        $this->authorize('manageContent', $submission->assignment->course);

        $submission = $action->handle(
            $submission,
            $request->user(),
            $request->validated('score'),
            $request->validated('feedback'),
        );

        return ApiResponse::success(new AssignmentSubmissionResource($submission), 'Submission graded.');
    }
}
