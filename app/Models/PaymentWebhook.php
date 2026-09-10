<?php

namespace App\Models;

use App\Enums\WebhookStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['provider', 'event_id', 'event_type', 'signature', 'payload_json', 'status', 'processed_at', 'error_message'])]
class PaymentWebhook extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'status' => WebhookStatus::class,
            'payload_json' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
