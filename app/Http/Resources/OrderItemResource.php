<?php

namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderItem */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title_snapshot' => $this->title_snapshot,
            'price_minor' => $this->price_minor,
            'discount_minor' => $this->discount_minor,
            'total_minor' => $this->total_minor,
            'currency' => $this->currency,
        ];
    }
}
