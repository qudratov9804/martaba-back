<?php

namespace App\Models;

use App\Enums\CourseLevel;
use App\Enums\CoursePricingType;
use App\Enums\CourseStatus;
use App\Enums\CourseVisibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'organization_id', 'category_id', 'created_by',
    'title', 'slug', 'short_description', 'description',
    'thumbnail_path', 'cover_path', 'intro_video_path',
    'level', 'language',
    'pricing_type', 'price_minor', 'discount_price_minor', 'currency',
    'status', 'visibility',
    'featured', 'certificate_enabled', 'reviews_enabled',
    'estimated_duration_minutes', 'published_at',
    'seo_title', 'seo_description', 'seo_keywords',
    'completion_rules_json', 'settings_json', 'metadata_json',
])]
class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'level' => CourseLevel::class,
            'pricing_type' => CoursePricingType::class,
            'status' => CourseStatus::class,
            'visibility' => CourseVisibility::class,
            'featured' => 'boolean',
            'certificate_enabled' => 'boolean',
            'reviews_enabled' => 'boolean',
            'published_at' => 'datetime',
            'completion_rules_json' => 'array',
            'settings_json' => 'array',
            'metadata_json' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<CourseInstructor, $this>
     */
    public function courseInstructors(): HasMany
    {
        return $this->hasMany(CourseInstructor::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructors')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps(false);
    }

    /**
     * @return HasMany<CourseSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<CourseLesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class);
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->created_by === $user->id
            || $this->courseInstructors()->where('user_id', $user->id)->exists();
    }
}
