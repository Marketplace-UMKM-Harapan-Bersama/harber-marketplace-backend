<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\CartResource;

use Midtrans\Snap;
use Midtrans\Config;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="Cart operations: view, remove, and checkout"
 * )
 */
class CartController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cart",
     *     tags={"Cart"},
     *     summary="Get authenticated user's cart items",
     *     security={{"passport":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="cart", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=5),
     *                 @OA\Property(property="quantity", type="integer", example=2),
     *                 @OA\Property(property="product", type="object",
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="Mouse Wireless"),
     *                     @OA\Property(property="price", type="number", format="float", example=120000)
     *                 )
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index()
    {
        $user = auth()->guard('api')->user();
        $cart = Cart::with('product')->where('user_id', $user->id)->paginate(15);

        return CartResource::collection($cart);
    }

    /**
     * @OA\Post(
     *     path="/api/products/{id}/add-to-cart",
     *     tags={"Cart"},
     *     summary="Add a product to the authenticated user's cart",
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product added to cart."),
     *             @OA\Property(property="cart", type="object",
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="quantity", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function addToCart(Request $request, $id)
    {
        $user = auth()->guard('api')->user();

        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($id);

        // Ambil semua cart item user yang sedang aktif
        $existingCartItems = Cart::with('product')
            ->where('user_id', $user->id)
            ->get();

        // Cek apakah ada item lain dari seller yang berbeda
        if ($existingCartItems->isNotEmpty()) {
            $currentSellerId = $product->seller_id;

            $hasDifferentSeller = $existingCartItems->contains(function ($item) use ($currentSellerId) {
                return $item->product->seller_id !== $currentSellerId;
            });

            if ($hasDifferentSeller) {
                return response()->json([
                    'message' => 'Cart contains products from a different seller. Please clear your cart before adding products from another seller.',
                    'error_type' => 'different_seller'
                ], 422);
            }
        }

        // Lanjut proses simpan
        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cart) {
            $cart->increment('quantity', $request->quantity);
        } else {
            $cart = Cart::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'seller_id' => $product->seller_id
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart.',
            'cart' => $cart
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/cart/product/{product_id}/increase",
     *     tags={"Cart"},
     *     summary="Increase quantity of a cart item by product ID",
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quantity increased.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quantity increased."),
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=5),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *         )
     *     ),
     *     @OA\Response(response=404, description="Cart item not found")
     * )
     */
    public function increaseByProduct($product_id)
    {
        $user = auth()->guard('api')->user();
        $cart = Cart::where('user_id', $user->id)->where('product_id', $product_id)->firstOrFail();

        $cart->increment('quantity');

        return response()->json([
            'message' => 'Quantity increased.',
            'cart' => $cart
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/cart/product/{product_id}/decrease",
     *     tags={"Cart"},
     *     summary="Decrease quantity of a cart item by product ID",
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quantity decreased.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Quantity decreased."),
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=5),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *         )
     *     ),
     *     @OA\Response(response=404, description="Cart item not found")
     * )
     */
    public function decreaseByProduct($product_id)
    {
        $user = auth()->guard('api')->user();
        $cart = Cart::where('user_id', $user->id)->where('product_id', $product_id)->firstOrFail();

        if ($cart->quantity > 1) {
            $cart->decrement('quantity');
            return response()->json([
                'message' => 'Quantity decreased.',
                'cart' => $cart
            ]);
        } else {
            $cart->delete();
            return response()->json([
                'message' => 'Item removed from cart as quantity reached 0.'
            ]);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/cart/{id}",
     *     tags={"Cart"},
     *     summary="Remove a product from the cart",
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Cart item ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product removed from cart.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Item not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy($id)
    {
        $user = auth()->guard('api')->user();
        $cart = Cart::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $cart->delete();

        return response()->json([
            'message' => 'Product removed from cart.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/cart/checkout",
     *     tags={"Cart"},
     *     summary="Checkout the cart and create an order",
     *     security={{"passport":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "shipping_address",
     *                 "shipping_city",
     *                 "shipping_province",
     *                 "shipping_postal_code",
     *                 "payment_method"
     *             },
     *             @OA\Property(property="shipping_address", type="string", example="Jl. Merdeka No. 123, Jakarta"),
     *             @OA\Property(property="shipping_city", type="string", example="Jakarta"),
     *             @OA\Property(property="shipping_province", type="string", example="DKI Jakarta"),
     *             @OA\Property(property="shipping_postal_code", type="string", example="10110"),
     *             @OA\Property(property="payment_method", type="string", enum={"cod", "transfer", "ewallet"}, example="cod"),
     *             @OA\Property(property="notes", type="string", example="Tolong dikirim siang hari.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Checkout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Checkout successful."),
     *             @OA\Property(property="order_id", type="integer", example=101)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cart is empty",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart is empty."),
     *             @OA\Property(property="error_type", type="string", example="empty_cart")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cart contains products from multiple sellers",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart contains products from multiple sellers. Please checkout one seller at a time."),
     *             @OA\Property(property="error_type", type="string", example="multiple_sellers")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Checkout failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Checkout failed."),
     *             @OA\Property(property="error", type="string", example="SQLSTATE[HY000]...")
     *         )
     *     )
     * )
     */
    public function checkout(Request $request)
    {
        $user = auth()->guard('api')->user();

        // Validasi request
        $validated = $request->validate([
            'shipping_address'     => 'required|string',
            'shipping_city'        => 'required|string',
            'shipping_province'    => 'required|string',
            'shipping_postal_code' => 'required|string',
            'notes'                => 'nullable|string',
        ]);

        // Ambil cart
        $cartItems = Cart::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty.',
                'error_type' => 'empty_cart'
            ], 400);
        }

        // Validasi semua product punya seller yang sama
        $sellerId = $cartItems->first()->product->seller_id;

        $hasMultipleSellers = $cartItems->contains(function ($item) use ($sellerId) {
            return $item->product->seller_id !== $sellerId;
        });

        if ($hasMultipleSellers) {
            return response()->json([
                'message' => 'Cart contains products from multiple sellers. Please checkout one seller at a time.',
                'error_type' => 'multiple_sellers'
            ], 422);
        }

        // Proses simpan order
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id'              => $user->id,
                'seller_id'            => $sellerId,
                'order_number'         => 'INV-' . strtoupper(uniqid()),
                'total_amount'         => $cartItems->sum(fn($item) => $item->product->price * $item->quantity),
                'shipping_cost'        => 0, // Bisa kamu isi dengan kalkulasi ongkir nanti
                'payment_method'       => '',
                'payment_status'       => 'pending',
                'order_status'         => 'pending',
                'shipping_address'     => $validated['shipping_address'],
                'shipping_city'        => $validated['shipping_city'],
                'shipping_province'    => $validated['shipping_province'],
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'notes'                => $validated['notes'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                ]);
            }

            Cart::where('user_id', $user->id)->delete();

            // Inisialisasi konfigurasi Midtrans
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Generate Snap token
            $snapPayload = [
                'transaction_details' => [
                    'order_id'     => $order->order_number,
                    'gross_amount' => (int) $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email'      => $user->email,
                ],
                'item_details' => $cartItems->map(function ($item) {
                    return [
                        'id'       => (string) $item->product_id,
                        'price'    => (int) $item->product->price,
                        'quantity' => $item->quantity,
                        'name'     => $item->product->name,
                    ];
                })->toArray(),
            ];

            $snapToken = Snap::getSnapToken($snapPayload);

            // Simpan token di order (opsional)
            $order->update(['snap_token' => $snapToken]);

            DB::commit();

            return response()->json([
                'message' => 'Checkout successful.',
                'order_id' => $order->id,
                'snap_token'  => $snapToken
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Checkout failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/clear-cart",
     *     tags={"Cart"},
     *     summary="Clear all items from the authenticated user's cart",
     *     security={{"passport":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All cart items deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="All cart items cleared.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function clear()
    {
        $user = auth()->guard('api')->user();

        Cart::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'All cart items cleared.'
        ]);
    }

}
