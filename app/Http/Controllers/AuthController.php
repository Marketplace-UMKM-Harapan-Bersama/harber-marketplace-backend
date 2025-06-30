<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketplaceUser; // Mengganti User menjadi MarketplaceUser
use App\Models\Seller;
use Laravel\Passport\Client;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $role = $request->role;

        if($role == 'seller') {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:marketplace_users,email', // Validasi unik di tabel marketplace_users
                'password' => 'required|min:6',
                'role' => 'required|in:seller,customer',
                'shop_name' => 'required|unique:sellers',
                'shop_url' => 'unique:sellers|nullable', // shop_url diubah menjadi nullable dan unik
                'shop_description' => 'nullable|string', // shop_description diubah menjadi nullable
            ]);
        }else{
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:marketplace_users,email', // Validasi unik di tabel marketplace_users
                'password' => 'required|min:6',
                'role' => 'required|in:seller,customer',
            ]);
        }


        \DB::beginTransaction();

        try {
            $user = MarketplaceUser::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => $request->role,
            ]);

            if ($request->role === 'seller') {
                $seller = Seller::create([
                    'user_id' => $user->id,
                    'shop_name' => $request->shop_name,
                    'shop_url' => $request->shop_url ?? null,
                    'shop_description' => $request->shop_description ?? null,
                ]);
            }

            \DB::commit();

            return response()->json([
                'message' => 'Seller registered successfully',
                'user' => $user,
                'seller_info' => isset($seller) ? $seller : null,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {

        $user=\App\Models\MarketplaceUser::where('email',$request->input('email'))
            ->first();

        if($user){
            if(\Hash::check($request->input('password'), $user->password)){

                $token = $user->createToken('YourApp')->accessToken;

                return array(
                    'token_type'=>'Bearer',
                    'expires_in'=>60*60*24*7,
                    'access_token'=>$token,
                    'refresh_token'=>''
                );
            }else{
                return response()->json(
                    [
                        'success'=>false,
                        'message'=>'Password wrong'
                    ]
                );
            }
        }else{
            return response()->json(
                [
                    'success'=>false,
                    'message'=>'User Not Found'
                ]
            );
        }

    }

}
