<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Actions\Quizzes\AnswerQuizQuestionAction;
use App\Actions\Quizzes\StartQuizAttemptAction;
use App\Actions\Quizzes\SubmitQuizAttemptAction;
use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitQuizAnswerRequest;
use App\Http\Resources\QuizAttemptResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizAttemptController extends Controller
{
    public function store(Request $request, Quiz $quiz, StartQuizAttemptAction $action): JsonResponse
    {
        $this->ensureEnrolled($request->user(), $quiz);

        $attempt = $action->handle($request->user(), $quiz);

        return ApiResponse::success(new QuizAttemptResource($attempt), 'Quiz attempt started.', status: 201);
    }

    public function show(Request $request, QuizAttempt $attempt): JsonResponse
    {
        $this->ensureOwnAttempt($request->user(), $attempt);

        return ApiResponse::success(new QuizAttemptResource($attempt->load('answers')), 'Attempt loaded.');
    }

    public function answer(
        SubmitQuizAnswerRequest $request,
        QuizAttempt $attempt,
        AnswerQuizQuestionAction $action
    ): JsonResponse {
        $this->ensureOwnAttempt($request->user(), $attempt);

        $answer = $action->handle($attempt, $request->validated());

        return ApiResponse::success($answer, 'Answer recorded.');
    }

    public function submit(Request $request, QuizAttempt $attempt, SubmitQuizAttemptAction $action): JsonResponse
    {
        $this->ensureOwnAttempt($request->user(), $attempt);

        $attempt = $action->handle($attempt);

        return ApiResponse::success(new QuizAttemptResource($attempt), 'Quiz submitted.');
    }

    private function ensureEnrolled(User $user, Quiz $quiz): void
    {
        $isEnrolled = $quiz->course->enrollments()
            ->where('student_id', $user->id)
            ->whereIn('status', [EnrollmentStatus::Active, EnrollmentStatus::Completed])
            ->exists();

        if (! $isEnrolled) {
            throw new AuthorizationException('You are not enrolled in this course.');
        }
    }

    private function ensureOwnAttempt(User $user, QuizAttempt $attempt): void
    {
        if ($attempt->student_id !== $user->id) {
            throw new AuthorizationException('This is not your quiz attempt.');
        }
    }
}
