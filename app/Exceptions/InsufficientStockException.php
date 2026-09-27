<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

/**
 * Se lanza cuando se intenta vender más unidades de las que hay en stock.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(public readonly Product $product, public readonly int $requested)
    {
        parent::__construct(
            $product->stock === 0
                ? "El producto \"{$product->name}\" está agotado."
                : "Stock insuficiente para \"{$product->name}\": pediste {$requested} y quedan {$product->stock}."
        );
    }
}
