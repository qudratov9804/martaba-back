<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    public function view(User $user, Enrollment $enrollment): bool
    {
        return $user->hasRole(RoleName::SuperAdmin->value)
            || $enrollment->student_id === $user->id
            || $enrollment->course->isOwnedBy($user);
    }
}
