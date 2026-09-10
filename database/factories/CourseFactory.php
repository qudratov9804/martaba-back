<?php

namespace Database\Factories;

use App\Enums\CourseLevel;
use App\Enums\CoursePricingType;
use App\Enums\CourseStatus;
use App\Enums\CourseVisibility;
use App\Models\Course;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'organization_id' => Organization::factory(),
            'category_id' => null,
            'created_by' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'level' => CourseLevel::AllLevels,
            'language' => 'en',
            'pricing_type' => CoursePricingType::Free,
            'price_minor' => 0,
            'currency' => 'USD',
            'status' => CourseStatus::Draft,
            'visibility' => CourseVisibility::Private,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => CourseStatus::Published,
            'visibility' => CourseVisibility::Public,
            'published_at' => now(),
        ]);
    }

    public function paid(int $priceMinor = 5000): static
    {
        return $this->state(fn () => [
            'pricing_type' => CoursePricingType::Paid,
            'price_minor' => $priceMinor,
        ]);
    }
}
