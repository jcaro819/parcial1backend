<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SaleApiController extends Controller
{
    /** GET /api/sales/{sale} */
    public function show(Sale $sale): SaleResource
    {
        return new SaleResource($sale->load('items'));
    }

    /**
     * POST /api/sales
     * Body JSON: { customer_name, customer_email, card_holder, card_number,
     *              card_expiration, card_cvv, items: [{product_id, quantity}] }
     */
    public function store(CheckoutRequest $request, SaleService $sales): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Agrupa por producto: [product_id => cantidad total]
        $lines = [];
        foreach ($request->input('items') as $item) {
            $lines[$item['product_id']] = ($lines[$item['product_id']] ?? 0) + (int) $item['quantity'];
        }

        try {
            $sale = $sales->register(
                $lines,
                ['name' => $request->customer_name, 'email' => $request->customer_email],
                ['holder' => $request->card_holder, 'number' => $request->card_number, 'expiration' => $request->card_expiration],
            );
        } catch (InsufficientStockException $e) {
            // 422 Unprocessable Content: la petición es válida pero no se puede cumplir.
            return response()->json([
                'message' => $e->getMessage(),
                'product_id' => $e->product->id,
                'available_stock' => $e->product->stock,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return (new SaleResource($sale))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
