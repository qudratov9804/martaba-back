<?php

namespace App\Enums;

enum RoleName: string
{
    case SuperAdmin = 'super_admin';
    case Teacher = 'teacher';
    case Student = 'student';
}
