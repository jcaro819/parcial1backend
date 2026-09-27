<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Carrito de compras guardado en la sesión del usuario.
 * En la sesión sólo se guarda [product_id => cantidad]; los precios SIEMPRE
 * se recalculan desde la base de datos (nunca se confía en datos del navegador).
 */
class CartService
{
    private const KEY = 'cart';

    public function __construct(private readonly Session $session) {}

    /** @return array<int,int> */
    public function raw(): array
    {
        return $this->session->get(self::KEY, []);
    }

    public function add(Product $product, int $quantity): void
    {
        $cart = $this->raw();
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $quantity;
        $this->session->put(self::KEY, $cart);
    }

    public function update(Product $product, int $quantity): void
    {
        $cart = $this->raw();

        if ($quantity <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = $quantity;
        }

        $this->session->put(self::KEY, $cart);
    }

    public function remove(Product $product): void
    {
        $cart = $this->raw();
        unset($cart[$product->id]);
        $this->session->put(self::KEY, $cart);
    }

    public function quantityOf(Product $product): int
    {
        return $this->raw()[$product->id] ?? 0;
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }

    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /**
     * Líneas del carrito con los productos y precios actuales de la BD.
     *
     * @return Collection<int, array{product: Product, quantity: int, unit_price: float, final_unit_price: float, line_total: float}>
     */
    public function items(): Collection
    {
        $cart = $this->raw();

        if ($cart === []) {
            return collect();
        }

        return Product::with('category')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->map(fn (Product $product) => [
                'product' => $product,
                'quantity' => $cart[$product->id],
                'unit_price' => (float) $product->price,
                'final_unit_price' => $product->final_price,
                'line_total' => round($product->final_price * $cart[$product->id], 2),
            ])
            ->values();
    }

    /** @return array{subtotal: float, discount: float, total: float} */
    public function totals(?Collection $items = null): array
    {
        $items ??= $this->items();

        $subtotal = round($items->sum(fn ($i) => $i['unit_price'] * $i['quantity']), 2);
        $total = round($items->sum('line_total'), 2);

        return ['subtotal' => $subtotal, 'discount' => round($subtotal - $total, 2), 'total' => $total];
    }
}
