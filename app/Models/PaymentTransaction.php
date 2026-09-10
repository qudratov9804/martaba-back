<?php

namespace App\Models;

use App\Enums\PaymentTransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'payment_id', 'type', 'status', 'amount_minor', 'currency',
    'provider_transaction_id', 'request_payload_json', 'response_payload_json', 'processed_at',
])]
class PaymentTransaction extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'type' => PaymentTransactionType::class,
            'request_payload_json' => 'array',
            'response_payload_json' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
