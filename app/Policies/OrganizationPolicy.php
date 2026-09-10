<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::OrganizationsView->value);
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->can(PermissionName::OrganizationsView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::OrganizationsCreate->value);
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->can(PermissionName::OrganizationsUpdate->value);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->can(PermissionName::OrganizationsDelete->value);
    }
}
