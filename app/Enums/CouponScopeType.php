<?php

namespace App\Enums;

enum CouponScopeType: string
{
    case All = 'all';
    case Course = 'course';
    case Category = 'category';
}
