<?php

namespace Database\Factories;

use App\Enums\LessonType;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseLesson>
 */
class CourseLessonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'course_id' => Course::factory(),
            'section_id' => CourseSection::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->sentence(),
            'lesson_type' => LessonType::Text,
            'sort_order' => 0,
            'is_preview' => false,
            'is_required' => true,
            'is_published' => false,
        ];
    }
}
