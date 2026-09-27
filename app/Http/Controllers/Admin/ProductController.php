<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestor de productos: CRUD completo (listar, crear, editar y eliminar).
 */
class ProductController extends Controller
{
    /** GET /admin/productos */
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->filter($request->only(['category', 'franchise', 'name']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /** GET /admin/productos/create */
    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /** POST /admin/productos */
    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->validated());

        return redirect()->route('admin.products.index')
            ->with('success', "Producto \"{$product->name}\" creado correctamente.");
    }

    /** GET /admin/productos/{product} → redirige al detalle público. */
    public function show(Product $product): RedirectResponse
    {
        return redirect()->route('catalog.show', $product);
    }

    /** GET /admin/productos/{product}/edit */
    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /** PUT /admin/productos/{product} */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('admin.products.index')
            ->with('success', "Producto \"{$product->name}\" actualizado correctamente.");
    }

    /** DELETE /admin/productos/{product} */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Producto \"{$product->name}\" eliminado.");
    }
}
