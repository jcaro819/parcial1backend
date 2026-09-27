<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'card_holder',
        'card_brand',
        'card_last_four',
        'card_expiration',
        'subtotal',
        'discount',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /** Una venta tiene muchas líneas de detalle (1 a N). */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /** Productos incluidos en la venta (N a N a través de sale_items). */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'sale_items')
            ->withPivot(['quantity', 'unit_price', 'discount_rate', 'final_unit_price', 'line_total'])
            ->withTimestamps();
    }

    /** Tarjeta enmascarada para mostrar: "VISA **** 1234". */
    public function maskedCard(): string
    {
        return $this->card_brand.' **** **** **** '.$this->card_last_four;
    }

    public function totalUnits(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
