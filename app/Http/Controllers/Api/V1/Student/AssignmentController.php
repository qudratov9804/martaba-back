<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Actions\Assignments\SubmitAssignmentAction;
use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\AssignmentSubmissionResource;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courseIds = $request->user()->enrollments()
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->pluck('course_id');

        $assignments = Assignment::query()->whereIn('course_id', $courseIds)->get();

        return ApiResponse::success(AssignmentResource::collection($assignments), 'Assignments loaded.');
    }

    public function submit(
        SubmitAssignmentRequest $request,
        Assignment $assignment,
        SubmitAssignmentAction $action
    ): JsonResponse {
        $isEnrolled = $assignment->course->enrollments()
            ->where('student_id', $request->user()->id)
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->exists();

        if (! $isEnrolled) {
            throw new AuthorizationException('You are not enrolled in this course.');
        }

        $submission = $action->handle($request->user(), $assignment, $request->validated());

        return ApiResponse::success(new AssignmentSubmissionResource($submission), 'Assignment submitted.', status: 201);
    }

    public function showSubmission(Request $request, AssignmentSubmission $submission): JsonResponse
    {
        if ($submission->student_id !== $request->user()->id) {
            throw new AuthorizationException('This is not your submission.');
        }

        return ApiResponse::success(new AssignmentSubmissionResource($submission->load('media')), 'Submission loaded.');
    }
}
