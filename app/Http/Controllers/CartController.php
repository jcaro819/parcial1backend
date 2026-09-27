<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Carrito de compras: ver, agregar, modificar cantidades y quitar productos.
 */
class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    /** GET /carrito */
    public function index(): View
    {
        $items = $this->cart->items();

        return view('cart.index', [
            'items' => $items,
            'totals' => $this->cart->totals($items),
        ]);
    }

    /** POST /carrito/{product} → agrega un producto con la cantidad elegida. */
    public function store(Request $request, Product $product): RedirectResponse
    {
        if (! $product->isAvailable()) {
            return back()->with('error', "\"{$product->name}\" está agotado y no se puede vender.");
        }

        $alreadyInCart = $this->cart->quantityOf($product);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.max(1, $product->stock - $alreadyInCart)],
        ], [
            'quantity.max' => "Sólo quedan {$product->stock} unidades (ya tienes {$alreadyInCart} en el carrito).",
        ]);

        $this->cart->add($product, (int) $data['quantity']);

        return redirect()->route('cart.index')
            ->with('success', "Agregaste {$data['quantity']} × \"{$product->name}\" al carrito.");
    }

    /** PUT /carrito/{product} → cambia la cantidad de un producto. */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.max(1, $product->stock)],
        ], [
            'quantity.max' => "Sólo quedan {$product->stock} unidades de \"{$product->name}\".",
        ]);

        $this->cart->update($product, (int) $data['quantity']);

        return redirect()->route('cart.index')->with('success', 'Cantidad actualizada.');
    }

    /** DELETE /carrito/{product} → quita un producto del carrito. */
    public function destroy(Product $product): RedirectResponse
    {
        $this->cart->remove($product);

        return redirect()->route('cart.index')->with('success', "Quitaste \"{$product->name}\" del carrito.");
    }

    /** DELETE /carrito → vacía el carrito. */
    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return redirect()->route('cart.index')->with('success', 'Carrito vaciado.');
    }
}
