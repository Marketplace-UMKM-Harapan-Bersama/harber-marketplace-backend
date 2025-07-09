<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\SellerResource;
use Illuminate\Http\Request;

class SellerController extends Controller
{
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
            'data' => new SellerResource($seller),
        ]);
    }

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
