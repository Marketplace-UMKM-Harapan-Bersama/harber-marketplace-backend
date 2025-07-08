<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $user = Auth::guard('api')->user();
        $cart = Cart::with('product')->where('user_id', $user->id)->get();

        return response()->json([
            'cart' => $cart
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/products/{id}/add-to-cart",
     *     tags={"Products"},
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
        $user = Auth::guard('api')->user(); // asumsi pakai passport & guard api

        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($id);

        $cart = Cart::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            ['quantity' => \DB::raw("quantity + {$request->quantity}")]
        );

        return response()->json([
            'message' => 'Product added to cart.',
            'cart' => $cart
        ]);
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
        $user = Auth::guard('api')->user();
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
     *             required={"shipping_address"},
     *             @OA\Property(property="shipping_address", type="string", example="Jl. Merdeka No. 123, Jakarta"),
     *             @OA\Property(property="city", type="string", example="Jakarta"),
     *             @OA\Property(property="province", type="string", example="DKI Jakarta"),
     *             @OA\Property(property="postal_code", type="string", example="10110")
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
     *             @OA\Property(property="message", type="string", example="Cart is empty.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Checkout failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Checkout failed."),
     *             @OA\Property(property="error", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function checkout()
    {
        $user = Auth::guard('api')->user();
        $cartItems = Cart::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $user->id,
                'total' => $cartItems->sum(fn($item) => $item->product->price * $item->quantity),
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Checkout successful.',
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Checkout failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
