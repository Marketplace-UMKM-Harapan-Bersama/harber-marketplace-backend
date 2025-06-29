<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Response;

/**
 * Controller untuk manajemen produk.
 * 
 * Menyediakan endpoint untuk menampilkan daftar produk (dengan pagination).
 */
class ProductController extends Controller
{
    /**
     * Menampilkan daftar produk dengan pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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
