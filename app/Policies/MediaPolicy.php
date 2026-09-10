<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function create(User $user): bool
    {
        return $user->can(PermissionName::LessonsManage->value) || $user->hasRole(RoleName::SuperAdmin->value);
    }

    public function view(User $user, Media $media): bool
    {
        return $user->hasRole(RoleName::SuperAdmin->value) || $media->uploaded_by === $user->id;
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->hasRole(RoleName::SuperAdmin->value) || $media->uploaded_by === $user->id;
    }
}
