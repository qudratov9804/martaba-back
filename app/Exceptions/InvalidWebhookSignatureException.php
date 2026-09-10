<?php

namespace App\Exceptions;

use Exception;

class InvalidWebhookSignatureException extends Exception
{
    protected $message = 'The webhook signature could not be verified.';
}
