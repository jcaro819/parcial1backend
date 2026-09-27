<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * API REST de productos: misma lógica que la web, pero responde JSON
 * en vez de HTML. Es lo que consumiría un frontend en React/Vue/Angular o una app móvil.
 */
class ProductApiController extends Controller
{
    /** GET /api/products?category=1&franchise=Star%20Wars&name=sable */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::with('category')
            ->filter($request->only(['category', 'franchise', 'name']))
            ->orderBy('name')
            ->paginate(min(max($request->integer('per_page', 12), 1), 100));

        return ProductResource::collection($products);
    }

    /** GET /api/products/{product} */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('category'));
    }

    /** POST /api/products → 201 Created */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated())->load('category');

        return (new ProductResource($product))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /** PUT /api/products/{product} */
    public function update(ProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return new ProductResource($product->load('category'));
    }

    /** DELETE /api/products/{product} → 204 No Content */
    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }
}
