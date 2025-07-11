<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ProductCategory;
use App\Http\Resources\ProductCategoryResource;

/**
 * @OA\Tag(
 *     name="Product Categories",
 *     description="List data product categories dan product category by slug"
 * )
 */
class ProductCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/product-categories",
     *     tags={"Product Categories"},
     *     summary="List all product categories",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index()
    {
        return ProductCategoryResource::collection(ProductCategory::where('is_active', true)->paginate(15));
    }

    /**
     * @OA\Get(
     *     path="/api/product-categories/{slug}",
     *     tags={"Product Categories"},
     *     summary="Get a product category by Slug",
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show($slug)
    {
        $productCategory = ProductCategory::where('slug', $slug)->firstOrFail()->load(['products' => function ($q) {
            $q->where('is_active', true);
        }])->loadCount('products');

        return new ProductCategoryResource($productCategory);
    }
}
