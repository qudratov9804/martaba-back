<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Actions\Quizzes\CreateQuestionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Quiz;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    public function store(StoreQuestionRequest $request, Quiz $quiz, CreateQuestionAction $action): JsonResponse
    {
        $this->authorize('manageContent', $quiz->course);

        $data = $request->validated();
        $options = $data['options'] ?? [];
        unset($data['options']);

        $question = $action->handle($quiz, $data, $options);

        return ApiResponse::success(new QuestionResource($question), 'Question added.', status: 201);
    }

    public function update(StoreQuestionRequest $request, Question $question): JsonResponse
    {
        $this->authorize('manageContent', $question->quiz->course);

        $data = $request->validated();
        unset($data['options']);

        $question->update($data);

        return ApiResponse::success(new QuestionResource($question->load('options')), 'Question updated.');
    }

    public function destroy(Question $question): JsonResponse
    {
        $this->authorize('manageContent', $question->quiz->course);

        $question->delete();

        return ApiResponse::success(null, 'Question deleted.');
    }
}
