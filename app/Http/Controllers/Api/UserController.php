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
        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => $request->user(),
        ]);
    }
}
