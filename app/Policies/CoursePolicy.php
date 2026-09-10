<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::CoursesView->value);
    }

    public function view(User $user, Course $course): bool
    {
        return $user->can(PermissionName::CoursesView->value)
            && ($this->isSuperAdmin($user) || $course->isOwnedBy($user));
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::CoursesCreate->value);
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can(PermissionName::CoursesUpdate->value)
            && ($this->isSuperAdmin($user) || $course->isOwnedBy($user));
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can(PermissionName::CoursesDelete->value)
            && ($this->isSuperAdmin($user) || $course->isOwnedBy($user));
    }

    public function publish(User $user, Course $course): bool
    {
        return $user->can(PermissionName::CoursesPublish->value)
            && ($this->isSuperAdmin($user) || $course->isOwnedBy($user));
    }

    public function archive(User $user, Course $course): bool
    {
        return $user->can(PermissionName::CoursesArchive->value)
            && ($this->isSuperAdmin($user) || $course->isOwnedBy($user));
    }

    public function manageContent(User $user, Course $course): bool
    {
        return $user->can(PermissionName::LessonsManage->value)
            && ($this->isSuperAdmin($user) || $course->isOwnedBy($user));
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasRole(RoleName::SuperAdmin->value);
    }
}
