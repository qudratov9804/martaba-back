<?php

namespace Database\Factories;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Organization;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'quiz_id' => Quiz::factory(),
            'type' => QuestionType::SingleChoice,
            'question_text' => fake()->sentence().'?',
            'points' => 1,
            'difficulty' => QuestionDifficulty::Medium,
            'sort_order' => 0,
        ];
    }
}
