<?php

namespace Database\Factories;

use App\Enums\QuizType;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->sentence(3),
            'type' => QuizType::Lesson,
            'passing_score' => 70,
            'max_attempts' => null,
            'is_required' => true,
        ];
    }
}
