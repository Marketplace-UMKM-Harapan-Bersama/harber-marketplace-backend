<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Get list of orders for the authenticated user",
     *     security={{"passport":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="total", type="number", format="float", example=250000),
     *                 @OA\Property(property="shipping_address", type="string", example="Jl. Merdeka 123"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-07T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::guard('api')->id())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'total', 'shipping_address', 'created_at']);

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     summary="Get order detail by ID",
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=101),
     *             @OA\Property(property="total", type="number", format="float", example=350000),
     *             @OA\Property(property="shipping_address", type="string", example="Jl. Merdeka No. 123"),
     *             @OA\Property(property="order_items", type="array", @OA\Items(
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="product_name", type="string", example="Laptop"),
     *                 @OA\Property(property="quantity", type="integer", example=1),
     *                 @OA\Property(property="price", type="number", format="float", example=15000000)
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function show($id)
    {
        $order = Order::with(['items.product'])->where('id', $id)->where('user_id', auth()->guard('api')->id())->firstOrFail();

        return response()->json([
            'id' => $order->id,
            'total' => $order->total,
            'shipping_address' => $order->shipping_address,
            'order_items' => $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? null,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            })
        ]);
    }


}
