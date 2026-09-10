<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreQuizRequest;
use App\Http\Requests\Teacher\UpdateQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Course;
use App\Models\Quiz;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class QuizController extends Controller
{
    public function index(Course $course): JsonResponse
    {
        $this->authorize('view', $course);

        $quizzes = $course->quizzes()->with('questions.options')->get();

        return ApiResponse::success(QuizResource::collection($quizzes), 'Quizzes loaded.');
    }

    public function store(StoreQuizRequest $request, Course $course): JsonResponse
    {
        $this->authorize('manageContent', $course);

        $quiz = $course->quizzes()->create($request->validated());

        return ApiResponse::success(new QuizResource($quiz), 'Quiz created.', status: 201);
    }

    public function show(Quiz $quiz): JsonResponse
    {
        $this->authorize('view', $quiz->course);

        return ApiResponse::success(new QuizResource($quiz->load('questions.options')), 'Quiz loaded.');
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): JsonResponse
    {
        $this->authorize('manageContent', $quiz->course);

        $quiz->update($request->validated());

        return ApiResponse::success(new QuizResource($quiz), 'Quiz updated.');
    }

    public function destroy(Quiz $quiz): JsonResponse
    {
        $this->authorize('manageContent', $quiz->course);

        $quiz->delete();

        return ApiResponse::success(null, 'Quiz deleted.');
    }
}
