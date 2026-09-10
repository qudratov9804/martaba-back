<?php

namespace App\Enums;

enum EnrollmentSource: string
{
    case Free = 'free';
    case Purchase = 'purchase';
    case Admin = 'admin';
    case Coupon = 'coupon';
    case Migration = 'migration';
}
