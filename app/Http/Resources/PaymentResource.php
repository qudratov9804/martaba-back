<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Payment */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'provider' => $this->provider,
            'status' => $this->status,
            'amount_minor' => $this->amount_minor,
            'currency' => $this->currency,
            'provider_payment_id' => $this->provider_payment_id,
            'failure_code' => $this->failure_code,
            'failure_message' => $this->failure_message,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
