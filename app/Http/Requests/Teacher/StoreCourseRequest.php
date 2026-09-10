<?php

namespace App\Http\Requests\Teacher;

use App\Enums\CourseLevel;
use App\Enums\CoursePricingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('organization_id', $this->user()->organization_id)],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('courses', 'slug')->where('organization_id', $this->user()->organization_id),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'level' => ['nullable', Rule::enum(CourseLevel::class)],
            'language' => ['nullable', 'string', 'max:5'],
            'pricing_type' => ['required', Rule::enum(CoursePricingType::class)],
            'price_minor' => ['required_if:pricing_type,paid', 'integer', 'min:0'],
            'discount_price_minor' => ['nullable', 'integer', 'min:0', 'lte:price_minor'],
            'currency' => ['nullable', 'string', 'size:3'],
            'certificate_enabled' => ['nullable', 'boolean'],
            'reviews_enabled' => ['nullable', 'boolean'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'completion_rules_json' => ['nullable', 'array'],
        ];
    }
}
