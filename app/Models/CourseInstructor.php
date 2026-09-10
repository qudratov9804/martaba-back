<?php

namespace App\Models;

use App\Enums\CourseInstructorRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['course_id', 'user_id', 'role', 'is_primary'])]
class CourseInstructor extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'role' => CourseInstructorRole::class,
            'is_primary' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
