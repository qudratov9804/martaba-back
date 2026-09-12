<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::UsersView->value);
    }

    public function view(User $user, User $target): bool
    {
        return $user->can(PermissionName::UsersView->value);
    }

    public function update(User $user, User $target): bool
    {
        return $user->can(PermissionName::UsersUpdate->value);
    }

    public function delete(User $user, User $target): bool
    {
        return $user->can(PermissionName::UsersDelete->value) && $user->id !== $target->id;
    }
}
