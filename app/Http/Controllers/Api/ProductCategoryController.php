<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Http\Resources\ProductCategoryResource;

/**
 * Controller untuk manajemen kategori produk.
 * 
 * Menyediakan endpoint untuk menampilkan daftar kategori produk (dengan pagination)
 * dan detail kategori produk.
 */
class ProductCategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori produk dengan pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Menampilkan detail kategori produk berdasarkan ID.
     *
     * @param  int  $id
     * @return \App\Http\Resources\ProductCategoryResource
     */
    public function show($id)
    {
        $category = ProductCategory::findOrFail($id);
        return new ProductCategoryResource($category);
    }
}
