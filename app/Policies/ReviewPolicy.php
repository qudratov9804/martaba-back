<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ReviewsView->value);
    }

    public function update(User $user, Review $review): bool
    {
        return $review->student_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $review->student_id === $user->id;
    }

    public function moderate(User $user, Review $review): bool
    {
        return $user->can(PermissionName::ReviewsModerate->value);
    }
}
