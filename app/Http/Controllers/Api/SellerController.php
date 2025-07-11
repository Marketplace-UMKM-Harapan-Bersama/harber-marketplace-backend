<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\SellerResource;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/seller/{id}",
     *     tags={"Auth"},
     *     summary="Get seller detail by ID",
     *     description="Retrieve seller information including related user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the seller",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Seller retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Seller retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="shop_name", type="string", example="Toko Jaya Abadi"),
     *                 @OA\Property(property="shop_url", type="string", example="tokojayaabadi"),
     *                 @OA\Property(property="shop_description", type="string", example="Menjual berbagai produk rumah tangga"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=9),
     *                     @OA\Property(property="name", type="string", example="Jamal Apriadi"),
     *                     @OA\Property(property="email", type="string", example="jamal@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid seller ID",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid seller ID"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="array", @OA\Items(type="string", example="The selected id is invalid."))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:sellers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid seller ID',
                'data' => $validator->errors(),
            ], 422);
        }

        $seller = Seller::with('user')->find($id);

        return response()->json([
            'message' => 'Seller retrieved successfully',
            'data' => SellerResource::make($seller)->except([
                'client_id',
                'client_secret'
            ]),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/seller",
     *     tags={"Auth"},
     *     summary="Get the seller data for the authenticated user",
     *     security={{"passport":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Seller retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Seller retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="shop_name", type="string", example="Toko Jaya Abadi"),
     *                 @OA\Property(property="shop_url", type="string", example="tokojayaabadi"),
     *                 @OA\Property(property="shop_description", type="string", example="Menjual berbagai produk rumah tangga"),
     *                 @OA\Property(property="client_id", type="string", example="abc123clientid"),
     *                 @OA\Property(property="client_secret", type="string", example="secret123"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=9),
     *                     @OA\Property(property="name", type="string", example="Jamal Apriadi"),
     *                     @OA\Property(property="email", type="string", example="jamal@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Seller not found for the current user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Seller not found for the current user.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function oauthSeller(Request $request)
    {
        $user = $request->user();

        $seller = Seller::where('user_id', $user->id)->first();

        if (!$seller) {
            return response()->json(['message' => 'Seller not found for the current user.'], 404);
        }

        return response()->json([
            'message' => 'Seller retrieved successfully',
            'data' => new SellerResource($seller),
        ]);
    }
}
