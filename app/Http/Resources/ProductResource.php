<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Define EXACTAMENTE qué campos de un producto se envían al frontend en JSON.
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'franchise' => $this->franchise,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'stock' => $this->stock,
            'available' => $this->isAvailable(),
            'is_on_promotion' => $this->is_on_promotion,
            'price' => (float) $this->price,
            'discount_rate' => $this->discount_rate,
            'final_price' => $this->final_price,
        ];
    }
}
