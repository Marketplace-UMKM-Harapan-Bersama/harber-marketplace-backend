<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Http\Resources\ProductResource;


/**
 * @OA\Tag(
 *      name="Products",
 *      description="List data product dan single product menggunakan slug"
 * )
 * 
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Get list of products",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        return ProductResource::collection(
            Product::where('is_active', true)->paginate(15)
        );
    }

    /**
     * @OA\Get(
     *     path="/api/products/{slug}",
     *     tags={"Products"},
     *     summary="Get product by Slug",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show($slug)
    {
        return new ProductResource(
            Product::where('slug', $slug)->with(['category', 'seller'])
                ->where('is_active', true)
                ->firstOrFail()
        );
    }
}
