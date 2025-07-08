<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Seller;
use Laravel\Passport\Token;

class ProductSyncController extends Controller
{
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'seller_product_id' => 'required',
            'name' => 'required|string',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category_id' => 'required|exists:product_categories,id',
        ]);

        // Ambil token yang sedang digunakan
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response()->json(['error' => 'No token provided.'], 401);
        }

        // Ambil token object dari DB
        $token = \DB::table('oauth_access_tokens')
            ->where('id', explode('.', $accessToken)[0]) // token format: token.payload.signature
            ->first();

        if (!$token) {
            return response()->json(['error' => 'Invalid token.'], 401);
        }

        $clientId = $token->client_id;

        // Cari seller berdasarkan client_id
        $seller = Seller::where('client_id', $clientId)->first();

        if (!$seller) {
            return response()->json(['error' => 'Seller not found for this client.'], 403);
        }

        // Simpan atau update produk
        $product = Product::updateOrCreate(
            [
                'seller_id' => $seller->id,
                'seller_product_id' => $validated['seller_product_id']
            ],
            array_merge($validated, [
                'seller_id' => $seller->id,
                'last_synced_at' => now(),
            ])
        );

        return response()->json([
            'message' => 'Product synced successfully',
            'product' => $product,
        ]);
    }
}
