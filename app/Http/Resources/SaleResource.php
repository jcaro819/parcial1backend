<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => ['name' => $this->customer_name, 'email' => $this->customer_email],
            'card' => $this->maskedCard(),
            'items' => $this->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount_rate' => (float) $item->discount_rate,
                'final_unit_price' => (float) $item->final_unit_price,
                'line_total' => (float) $item->line_total,
            ]),
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
