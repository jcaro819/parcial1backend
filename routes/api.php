<?php

use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\SaleApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de la API (devuelven JSON). Todas llevan el prefijo /api
|--------------------------------------------------------------------------
| Estas rutas NO usan sesión ni token CSRF: están pensadas para que un
| frontend separado (React, Vue, app móvil, Postman...) se comunique con el backend.
*/

Route::name('api.')->group(function () {
    Route::get('/categories', [CategoryApiController::class, 'index'])->name('categories.index');
    Route::apiResource('products', ProductApiController::class);
    Route::post('/sales', [SaleApiController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleApiController::class, 'show'])->name('sales.show');
});
