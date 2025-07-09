<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketplaceUser; // Mengganti User menjadi MarketplaceUser
use App\Models\Seller;
// use Laravel\Passport\Client;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Registrasi dan Login Customer / Seller"
 * )
 */
class SellerController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Register a new user (customer or seller)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "role"},
     *             @OA\Property(property="name", type="string", example="Andi"),
     *             @OA\Property(property="email", type="string", format="email", example="andi@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="role", type="string", enum={"seller", "customer"}, example="seller"),
     *             @OA\Property(property="shop_name", type="string", example="Toko Andi"),
     *             @OA\Property(property="shop_url", type="string", example="toko-andi"),
     *             @OA\Property(property="shop_description", type="string", example="Menjual barang elektronik")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Seller registered successfully"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Andi"),
     *                 @OA\Property(property="email", type="string", example="andi@example.com"),
     *                 @OA\Property(property="role", type="string", example="seller")
     *             ),
     *             @OA\Property(property="seller_info", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="shop_name", type="string", example="Toko Andi"),
     *                 @OA\Property(property="shop_url", type="string", example="toko-andi")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Registration failed"
     *     )
     * )
     */
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

                // $client = Client::create([
                //     'name' => 'Client for Store: ' . $request->shop_name,
                //     'redirect_uris' => '',
                //     'revoked' => false,
                //     'secret' => Str::random(40), // generate secret manual
                //     'grant_types' => 'client_credentials', // untuk Passport v11+
                // ]);

                $seller = Seller::create([
                    'user_id' => $user->id,
                    'shop_name' => $request->shop_name,
                    'shop_url' => $request->shop_url ?? null,
                    'shop_description' => $request->shop_description ?? null,
                    'client_id' => $user->id,
                    'client_secret' => Str::random(40),
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

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login for customer/seller and get access token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="andi@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=604800),
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1Q..."),
     *             @OA\Property(property="refresh_token", type="string", example="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Password wrong / User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Password wrong")
     *         )
     *     )
     * )
     */
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