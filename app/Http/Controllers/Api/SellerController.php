<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\SellerResource;

class SellerController extends Controller
{
    public function show($id)
    {
        // Validate the ID
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:sellers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid seller ID',
                'data' => $validator->errors(),
            ], 422);
        }

        // Load seller with user relationship
        $seller = Seller::with('user')->find($id);

        return response()->json([
            'message' => 'Seller retrieved successfully',
            'data' => new SellerResource($seller),
        ]);
    }
}
