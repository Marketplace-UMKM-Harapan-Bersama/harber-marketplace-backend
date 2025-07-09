<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Seller;
use Laravel\Passport\Token;

use Carbon\Carbon;

class ProductSyncController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/product/sync",
     *     tags={"External Integration"},
     *     summary="Sync a single product from external store (by seller client ID and client secret)",
     *     description="Digunakan oleh toko eksternal untuk mengirim data produk ke marketplace pusat.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id", "client_secret", "seller_product_id", "name", "price","category_id", "stock"},
     *             @OA\Property(property="client_id", type="string", example="abc123"),
     *             @OA\Property(property="client_secret", type="string", example="asdf2342432323"),
     *             @OA\Property(property="seller_product_id", type="string", example="SP001"),
     *             @OA\Property(property="name", type="string", example="Tas Ransel Hitam"),
     *             @OA\Property(property="description", type="string", example="Tas untuk kerja & sekolah"),
     *             @OA\Property(property="price", type="number", format="float", example=159000),
     *             @OA\Property(property="stock", type="integer", example=10),
     *             @OA\Property(property="sku", type="string", example="SKU-001"),
     *             @OA\Property(property="image_url", type="string", example="https://example.com/image.jpg"),
     *             @OA\Property(property="weight", type="number", format="float", example=0.8),
     *             @OA\Property(property="category_id", type="number", format="float", example=1),
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
            'client_id' => 'required|string',
            'client_secret' => 'required',
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

        $seller = Seller::where('client_id', $request->client_id)
            ->where('client_secret', $request->client_secret)
            ->first();

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
                'category_id' => $request->category_id,
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
