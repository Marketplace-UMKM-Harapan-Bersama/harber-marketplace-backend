<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Seller;
use Laravel\Passport\Client;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'shop_name' => 'required|unique:sellers',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'seller',
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'name' => $request->shop_name,
            'redirect' => '',
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'secret' => Str::random(40),
        ]);

        $seller = Seller::create([
            'user_id' => $user->id,
            'shop_name' => $request->shop_name,
            'shop_url' => $request->shop_url ?? null,
            'shop_description' => $request->shop_description ?? null,
            'client_id' => $client->id,
            'client_secret' => $client->secret,
        ]);

        return response()->json([
            'message' => 'Seller registered successfully',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
        ]);
    }
}
