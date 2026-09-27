<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Catálogo de ventas (página principal) y página de detalle del producto.
 */
class CatalogController extends Controller
{
    /** GET /  → catálogo con filtros por categoría, nombre y franquicia. */
    public function index(Request $request): View
    {
        $filters = $request->only(['category', 'franchise', 'name']);

        $products = Product::with('category')
            ->filter($filters)
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString(); // mantiene los filtros al cambiar de página

        return view('catalog.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'franchises' => Product::franchises(),
            'filters' => $filters,
        ]);
    }

    /** GET /productos/{product} → detalle y selector de cantidad. */
    public function show(Product $product, CartService $cart): View
    {
        $product->load('category');

        return view('catalog.show', [
            'product' => $product,
            'inCart' => $cart->quantityOf($product),
        ]);
    }
}
