<?php

namespace App\Enums;

enum PaymentTransactionType: string
{
    case Authorize = 'authorize';
    case Capture = 'capture';
    case Payment = 'payment';
    case Refund = 'refund';
    case PartialRefund = 'partial_refund';
    case Void = 'void';
}
