<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;

use App\Jobs\SendOrderToExternalStore;

class PaymentCallbackController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/midtrans/callback",
     *     tags={"Payment"},
     *     summary="Handle Midtrans payment notification callback",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Midtrans Notification Payload",
     *         @OA\JsonContent(
     *             @OA\Property(property="transaction_status", type="string", example="settlement"),
     *             @OA\Property(property="order_id", type="string", example="INV-64EE1F10A8E35"),
     *             @OA\Property(property="payment_type", type="string", example="bank_transfer"),
     *             @OA\Property(property="fraud_status", type="string", example="accept")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Callback handled successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function handle(Request $request)
    {
        // Optional logging
        Log::info('Midtrans Callback Received', $request->all());

        // $notif = new Notification();

        // $transactionStatus = $notif->transaction_status;
        // $paymentType       = $notif->payment_type;
        // $orderId           = $notif->order_id;
        // $fraudStatus       = $notif->fraud_status;

        $signatureKey = $request->input('signature_key');
        $transactionStatus = $request->input('transaction_status');
        $paymentType       = $request->input('payment_type');
        $orderId           = $request->input('order_id');
        $fraudStatus       = $request->input('fraud_status');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $serverKey = config('midtrans.server_key');

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($signatureKey !== $expectedSignature) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Find order by order_number
        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Update status berdasarkan transaction_status dari Midtrans
        switch ($transactionStatus) {
            case 'capture':
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'challenge') {
                        $order->payment_status = 'challenge';
                    } else {
                        $order->payment_status = 'paid';
                    }
                }

                $order->save();
                
                break;

            case 'settlement':
                $order->payment_status = 'paid';

                $order->save();

                // Dispatch job ke queue
                SendOrderToExternalStore::dispatch($order);

                break;

            case 'pending':
                $order->payment_status = 'pending';

                $order->save();
                break;

            case 'deny':
            case 'expire':
            case 'cancel':
                $order->payment_status = $transactionStatus;

                $order->save();
                break;
        }

        return response()->json(['message' => 'Callback processed successfully']);
    }
}
