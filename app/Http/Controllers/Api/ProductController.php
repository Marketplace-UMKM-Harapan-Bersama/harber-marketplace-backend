<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::latest()->paginate(10);

        return response()->json([
            'data' => ProductResource::collection($products),
            'status' => 200,
            'message' => 'List Data Product'
        ], Response::HTTP_OK);
    }
}
