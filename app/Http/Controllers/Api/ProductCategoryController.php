<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Http\Resources\ProductCategoryResource;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::latest()->paginate(10);

        return response()->json([
            'status' => 200,
            'message' => 'List Data Product Category',
            'data' => ProductCategoryResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ]
        ], 200);
    }

    public function show($id)
    {
        $category = ProductCategory::findOrFail($id);
        return new ProductCategoryResource($category);
    }
}
