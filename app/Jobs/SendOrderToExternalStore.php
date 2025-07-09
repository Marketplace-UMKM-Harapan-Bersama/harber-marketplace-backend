<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\Order;
use App\Models\Seller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderToExternalStore implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->withoutRelations();
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"External Integration"},
     *     summary="Terima data order dari sistem eksternal setelah pembayaran selesai",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="order_number", type="string", example="INV-64EE1F10A8E35"),
     *             @OA\Property(property="total_amount", type="number", example=250000),
     *             @OA\Property(property="shipping_address", type="string", example="Jl. Merdeka 123"),
     *             @OA\Property(property="shipping_city", type="string", example="Jakarta"),
     *             @OA\Property(property="shipping_province", type="string", example="DKI Jakarta"),
     *             @OA\Property(property="shipping_postal_code", type="string", example="10110"),
     *             @OA\Property(property="notes", type="string", example="Bungkus rapi"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=5),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="price", type="number", example=125000)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order berhasil diterima dari sistem eksternal"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validasi gagal"
     *     )
     * )
     */
    public function handle(): void
    {
        try {
            $order = Order::with('items','items.product')->findOrFail($this->order->id);

            $sellerId = optional($order->items->first()->product)->seller_id;
            $seller = Seller::find($sellerId);

            $payload = [
                'order_number'        => $order->order_number,
                'total_amount'        => $order->total_amount,
                'shipping_address'    => $order->shipping_address,
                'shipping_city'       => $order->shipping_city,
                'shipping_province'   => $order->shipping_province,
                'shipping_postal_code'=> $order->shipping_postal_code,
                'notes'               => $order->notes,
                'items'               => $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'price'      => $item->price,
                    ];
                }),
            ];

            $response = Http::post($seller->shop_url.'/api/orders', $payload);

            if ($response->successful()) {
                Log::info('Order sent to toko online', ['order_id' => $order->id]);
                // Optionally update synced flag:
                // $order->update(['synced_to_store' => true]);
            } else {
                Log::warning('Failed sending order to toko online', [
                    'response' => $response->body(),
                    'order_id' => $order->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Job failed to send order', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // allow retry
        }
    }
}
