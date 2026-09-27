<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Lógica de negocio del registro de una venta.
 * Se usa tanto desde el checkout web (Blade) como desde la API JSON.
 */
class SaleService
{
    /**
     * Registra una venta.
     *
     * @param  array<int,int>  $lines     [product_id => cantidad]
     * @param  array{name:string,email:string}  $customer
     * @param  array{holder:string,number:string,expiration:string}  $card
     *
     * @throws InsufficientStockException
     */
    public function register(array $lines, array $customer, array $card): Sale
    {
        $lines = array_filter($lines, fn ($qty) => (int) $qty > 0);

        if ($lines === []) {
            throw new InvalidArgumentException('La venta debe contener al menos un producto.');
        }

        // Todo dentro de una transacción: o se guarda TODO (venta, detalle y
        // descuento de stock) o no se guarda NADA si algo falla.
        return DB::transaction(function () use ($lines, $customer, $card) {
            // lockForUpdate bloquea las filas hasta el fin de la transacción para
            // que dos compras simultáneas no vendan la misma última unidad.
            $products = Product::whereIn('id', array_keys($lines))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 1) Verificar que haya stock suficiente para cada producto.
            foreach ($lines as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product) {
                    throw new InvalidArgumentException("El producto #{$productId} no existe.");
                }

                if (! $product->hasStockFor((int) $quantity)) {
                    throw new InsufficientStockException($product, (int) $quantity);
                }
            }

            // 2) Calcular precios (con descuento si corresponde) ANTES de descontar
            //    el stock, porque la promoción depende del stock actual.
            $items = [];
            $subtotal = 0.0;
            $total = 0.0;

            foreach ($lines as $productId => $quantity) {
                $product = $products[$productId];
                $quantity = (int) $quantity;
                $lineTotal = round($product->final_price * $quantity, 2);

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'discount_rate' => $product->discount_rate,
                    'final_unit_price' => $product->final_price,
                    'line_total' => $lineTotal,
                ];

                $subtotal += (float) $product->price * $quantity;
                $total += $lineTotal;
            }

            // 3) Registrar la venta con comprador, tarjeta y total a pagar.
            $number = preg_replace('/\D/', '', $card['number']);

            $sale = Sale::create([
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'card_holder' => $card['holder'],
                'card_brand' => self::detectCardBrand($number),
                'card_last_four' => substr($number, -4),
                'card_expiration' => $card['expiration'],
                'subtotal' => round($subtotal, 2),
                'discount' => round($subtotal - $total, 2),
                'total' => round($total, 2),
            ]);

            $sale->items()->createMany($items);

            // 4) Descontar el stock vendido.
            foreach ($lines as $productId => $quantity) {
                $products[$productId]->decrement('stock', (int) $quantity);
            }

            return $sale->load('items');
        });
    }

    public static function detectCardBrand(string $number): string
    {
        return match (true) {
            (bool) preg_match('/^4/', $number) => 'VISA',
            (bool) preg_match('/^(5[1-5]|2[2-7])/', $number) => 'MASTERCARD',
            (bool) preg_match('/^3[47]/', $number) => 'AMEX',
            default => 'OTRA',
        };
    }
}
