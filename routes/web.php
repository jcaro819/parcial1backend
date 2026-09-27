<?php

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas web (devuelven HTML generado con Blade)
|--------------------------------------------------------------------------
| Verbo HTTP + URL  →  Controlador@método   (nombre de la ruta)
| Ejecuta `php artisan route:list` para verlas todas.
*/

// Catálogo de ventas (página principal) y página de producto
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/productos/{product}', [CatalogController::class, 'show'])->name('catalog.show');

// Carrito de compras
Route::prefix('carrito')->name('cart.')->controller(CartController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::delete('/', 'clear')->name('clear');
    Route::post('/{product}', 'store')->name('store');
    Route::put('/{product}', 'update')->name('update');
    Route::delete('/{product}', 'destroy')->name('destroy');
});

// Checkout
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Ventas registradas (historial y comprobante)
Route::get('/ventas', [SaleController::class, 'index'])->name('sales.index');
Route::get('/ventas/{sale}', [SaleController::class, 'show'])->name('sales.show');

// Gestor de productos (CRUD completo con Route::resource → 7 rutas)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('productos', ProductController::class)
        ->parameters(['productos' => 'product'])
        ->names('products');
});
