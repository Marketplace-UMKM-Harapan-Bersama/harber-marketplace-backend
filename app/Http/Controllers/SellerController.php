<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketplaceUser; // Mengganti User menjadi MarketplaceUser
use App\Models\Seller;
use Laravel\Passport\Client;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:marketplace_users,email', // Validasi unik di tabel marketplace_users
            'password' => 'required|min:6',
            'role' => 'required|in:seller,customer',
        ]);

        $user = MarketplaceUser::create([ // Menggunakan MarketplaceUser
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        if ($request->role === 'seller') {
             $request->validate([
            'shop_name' => 'required|unique:sellers',
            'shop_url' => 'unique:sellers|nullable', // shop_url diubah menjadi nullable dan unik
            'shop_description' => 'nullable|string', // shop_description diubah menjadi nullable
        ]);
            $seller = Seller::create([
            'user_id' => $user->id,
            'shop_name' => $request->shop_name,
            'shop_url' => $request->shop_url ?? null,
            'shop_description' => $request->shop_description ?? null,
        ]);
        
        // Buat client Passport untuk seller baru
        // Ini adalah contoh, Anda mungkin perlu menyesuaikan bagaimana Anda ingin Passport Client dibuat.
        // Biasanya, client_id dan client_secret tidak dibuat secara langsung di sini.
        // $client = new Client();
        // $client->user_id = $user->id;
        // $client->name = $request->shop_name . ' Personal Access Client';
        // $client->redirect = env('APP_URL'); // Ganti dengan URL redirect yang sesuai
        // $client->personal_access_client = true;
        // $client->password_client = false;
        // $client->revoked = false;
        // $client->save();

        // $seller->client_id = $client->id;
        // $seller->client_secret = $client->secret;
        // $seller->save();
        
        }

        return response()->json([
            'message' => 'Seller registered successfully',
            'user' => $user,
            'seller_info' => isset($seller) ? $seller : null,
        ]);
    }

}