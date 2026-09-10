<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Actions\Quizzes\GradeQuizAnswerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\GradeQuizAnswerRequest;
use App\Http\Resources\QuizAnswerResource;
use App\Models\QuizAnswer;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class QuizAnswerController extends Controller
{
    public function grade(GradeQuizAnswerRequest $request, QuizAnswer $answer, GradeQuizAnswerAction $action): JsonResponse
    {
        $this->authorize('manageContent', $answer->attempt->quiz->course);

        $answer = $action->handle($answer, (float) $request->validated('points_awarded'));

        return ApiResponse::success(new QuizAnswerResource($answer), 'Answer graded.');
    }
}
