<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\View\View;

/**
 * Historial de ventas registradas y comprobante de cada venta.
 */
class SaleController extends Controller
{
    /** GET /ventas */
    public function index(): View
    {
        $sales = Sale::withCount('items')->latest()->paginate(15);

        return view('sales.index', compact('sales'));
    }

    /** GET /ventas/{sale} → comprobante de la venta. */
    public function show(Sale $sale): View
    {
        $sale->load('items.product');

        return view('sales.show', compact('sale'));
    }
}
