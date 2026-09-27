<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /** Stock desde el cual (exclusivo) un producto entra en "Promoción". */
    public const PROMOTION_STOCK_THRESHOLD = 20;

    /** Descuento aplicado a los productos en promoción (10%). */
    public const PROMOTION_DISCOUNT_RATE = 0.10;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'franchise',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    /**
     * Atributos calculados que se incluyen al convertir el modelo a array/JSON.
     */
    protected $appends = ['is_on_promotion', 'discount_rate', 'final_price'];

    /* ----------------------------------------------------------------
     |  Relaciones
     * ---------------------------------------------------------------- */

    /** Un producto pertenece a una categoría (N a 1). */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Líneas de venta en que aparece este producto. */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /** Ventas que incluyen este producto (N a N a través de sale_items). */
    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'sale_items')
            ->withPivot(['quantity', 'unit_price', 'discount_rate', 'final_unit_price', 'line_total'])
            ->withTimestamps();
    }

    /* ----------------------------------------------------------------
     |  Reglas de negocio (encapsuladas en el modelo = POO)
     * ---------------------------------------------------------------- */

    /** Regla: stock > 20 => el producto está en "Promoción". */
    protected function isOnPromotion(): Attribute
    {
        return Attribute::get(fn (): bool => $this->stock > self::PROMOTION_STOCK_THRESHOLD);
    }

    /** Porcentaje de descuento vigente (0.10 o 0). */
    protected function discountRate(): Attribute
    {
        return Attribute::get(fn (): float => $this->is_on_promotion ? self::PROMOTION_DISCOUNT_RATE : 0.0);
    }

    /** Monto descontado por unidad. */
    protected function discountAmount(): Attribute
    {
        return Attribute::get(fn (): float => round((float) $this->price * $this->discount_rate, 2));
    }

    /** Precio final por unidad que paga el cliente. */
    protected function finalPrice(): Attribute
    {
        return Attribute::get(fn (): float => round((float) $this->price - $this->discount_amount, 2));
    }

    /** Regla: un producto con stock 0 no puede venderse. */
    public function isAvailable(): bool
    {
        return $this->stock > 0;
    }

    /** ¿Hay stock suficiente para vender esta cantidad? */
    public function hasStockFor(int $quantity): bool
    {
        return $quantity > 0 && $this->stock >= $quantity;
    }

    /* ----------------------------------------------------------------
     |  Scopes (filtros de búsqueda reutilizables)
     * ---------------------------------------------------------------- */

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['category'] ?? null, fn (Builder $q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($filters['franchise'] ?? null, fn (Builder $q, $franchise) => $q->where('franchise', $franchise))
            ->when($filters['name'] ?? null, fn (Builder $q, $name) => $q->where('name', 'like', '%'.$name.'%'));
    }

    /** Lista de franquicias existentes (para el select de filtros). */
    public static function franchises(): array
    {
        return static::query()->distinct()->orderBy('franchise')->pluck('franchise')->all();
    }
}
