<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\Seller;

class ProductCategorySyncController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/product-category/sync",
     *     tags={"External Integration"},
     *     summary="Sync a single product from external store (by seller client ID and client secret)",
     *     description="Digunakan oleh toko eksternal untuk mengirim data produk ke marketplace pusat.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id", "client_secret", "seller_product_id", "name", "price","category_id", "stock"},
     *             @OA\Property(property="client_id", type="string", example="abc123"),
     *             @OA\Property(property="client_secret", type="string", example="asdf2342432323"),
     *             @OA\Property(property="seller_product_category_id", type="string", example="SP001"),
     *             @OA\Property(property="name", type="string", example="Tas Ransel Hitam"),
     *             @OA\Property(property="description", type="string", example="Tas untuk kerja & sekolah"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product synced successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product category synced successfully."),
     *             @OA\Property(property="product_category_id", type="integer", example=12)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Seller not found"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function sync_product_category(Request $request)
    {
        $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required',
            'seller_product_category_id' => 'required|string',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $seller = Seller::where('client_id', $request->client_id)
            ->where('client_secret', $request->client_secret)
            ->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller not found.'
            ], 404);
        }

        $productCategory = ProductCategory::updateOrCreate(
            [
                'seller_id' => $seller->id,
                'seller_product_category_id' => $request->seller_product_category_id
            ],
            [
                'name' => $request->name,
                'description' => $request->description ?? '',
                'is_active' => $request->is_active ?? true
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Product category synced successfully.',
            'product_category_id' => $productCategory->id
        ]);
    }
}
