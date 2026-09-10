<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case Manual = 'manual';
    case Click = 'click';
    case Payme = 'payme';
    case Uzum = 'uzum';
    case Stripe = 'stripe';
    case PayPal = 'paypal';
}
