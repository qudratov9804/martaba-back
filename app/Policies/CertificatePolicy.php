<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    public function view(User $user, Certificate $certificate): bool
    {
        return $certificate->student_id === $user->id
            || ($user->hasRole(RoleName::SuperAdmin->value) && $user->can(PermissionName::CertificatesView->value));
    }

    public function reissue(User $user, Certificate $certificate): bool
    {
        return $user->can(PermissionName::CertificatesReissue->value);
    }

    public function revoke(User $user, Certificate $certificate): bool
    {
        return $user->can(PermissionName::CertificatesRevoke->value);
    }
}
