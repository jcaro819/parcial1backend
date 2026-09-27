<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryApiController extends Controller
{
    /** GET /api/categories */
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::withCount('products')->orderBy('name')->get());
    }
}
