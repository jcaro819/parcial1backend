<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Checkout: pide los datos del cliente y de la tarjeta y registra la venta.
 */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly SaleService $sales,
    ) {}

    /** GET /checkout */
    public function create(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('catalog.index')->with('error', 'Tu carrito está vacío.');
        }

        $items = $this->cart->items();

        return view('checkout.create', [
            'items' => $items,
            'totals' => $this->cart->totals($items),
        ]);
    }

    /** POST /checkout → procesa la orden. */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('catalog.index')->with('error', 'Tu carrito está vacío.');
        }

        try {
            $sale = $this->sales->register(
                $this->cart->raw(),
                ['name' => $request->customer_name, 'email' => $request->customer_email],
                [
                    'holder' => $request->card_holder,
                    'number' => $request->card_number,
                    'expiration' => $request->card_expiration,
                ],
            );
        } catch (InsufficientStockException $e) {
            // No se guardó nada (la transacción hizo rollback): se vuelve al carrito.
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        $this->cart->clear();

        return redirect()->route('sales.show', $sale)
            ->with('success', '¡Compra realizada con éxito! Gracias por comprar en NerdVault.');
    }
}
