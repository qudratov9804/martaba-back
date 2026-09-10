<?php

namespace App\Actions\Quizzes;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class CreateQuestionAction
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<array{text: string, is_correct?: bool}>  $options
     */
    public function handle(Quiz $quiz, array $data, array $options = []): Question
    {
        return DB::transaction(function () use ($quiz, $data, $options) {
            $question = $quiz->questions()->create([
                ...$data,
                'organization_id' => $quiz->course->organization_id,
            ]);

            foreach ($options as $index => $option) {
                $question->options()->create([
                    'text' => $option['text'],
                    'is_correct' => $option['is_correct'] ?? false,
                    'sort_order' => $index,
                ]);
            }

            return $question->load('options');
        });
    }
}
