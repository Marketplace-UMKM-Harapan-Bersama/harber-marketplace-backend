<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceUser;
use App\Http\Resources\MarketplaceUserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:marketplace_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid user ID',
                'data' => $validator->errors(),
            ], 422);
        }

        $user = MarketplaceUser::find($id);

        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => new MarketplaceUserResource($user),
        ]);
    }

    public function oauthUser(Request $request) {
        $user = MarketplaceUser::find($request->user()->id);

        // Jika role seller, load seller relation
        if ($user && $user->role === 'seller') {
            $user->load('seller');
        }

        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => new MarketplaceUserResource($user),
        ]);
    }

    /**
     * Logout the authenticated user by revoking the access token.
     *
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout authenticated user (revoke access token)",
     *     security={{"passport":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }
}
