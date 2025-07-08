<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Http\Resources\ProductResource;


/**
 * @OA\Tag(name="Products")
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
        return ProductResource::collection(Product::paginate(15));
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
            Product::where('slug', $slug)->with(['category', 'seller'])->firstOrFail()
        );
    }

    /**
     * @OA\Post(
     *     path="/api/product/sync",
     *     tags={"Products"},
     *     summary="Sync a single product from external store (by seller client ID)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"seller_client_id", "seller_product_id", "name", "price", "stock"},
     *             @OA\Property(property="seller_client_id", type="string", example="abc123"),
     *             @OA\Property(property="seller_product_id", type="string", example="SP001"),
     *             @OA\Property(property="name", type="string", example="Tas Ransel Hitam"),
     *             @OA\Property(property="description", type="string", example="Tas untuk kerja & sekolah"),
     *             @OA\Property(property="price", type="number", format="float", example=159000),
     *             @OA\Property(property="stock", type="integer", example=10),
     *             @OA\Property(property="sku", type="string", example="SKU-001"),
     *             @OA\Property(property="image_url", type="string", example="https://example.com/image.jpg"),
     *             @OA\Property(property="weight", type="number", format="float", example=0.8),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product synced successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product synced successfully."),
     *             @OA\Property(property="product_id", type="integer", example=12)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Seller not found"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function syncSingleProduct(Request $request)
    {
        $request->validate([
            'seller_client_id' => 'required|string',
            'seller_product_id' => 'required|string',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'nullable|string',
            'sku' => 'nullable|string',
            'image_url' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $seller = Seller::where('client_id', $request->seller_client_id)->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller not found.'
            ], 404);
        }

        $product = Product::updateOrCreate(
            [
                'seller_id' => $seller->id,
                'seller_product_id' => $request->seller_product_id
            ],
            [
                'name' => $request->name,
                'description' => $request->description ?? '',
                'price' => $request->price,
                'stock' => $request->stock,
                'sku' => $request->sku,
                'image_url' => $request->image_url,
                'weight' => $request->weight,
                'is_active' => $request->is_active ?? true,
                'last_synced_at' => Carbon::now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Product synced successfully.',
            'product_id' => $product->id
        ]);
    }
}
