<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'seller_id' => $this->seller_id,
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'shipping_cost' => $this->shipping_cost,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'order_status' => $this->order_status,
            'shipping_address' => $this->shipping_address,
            'shipping_city' => $this->shipping_city,
            'shipping_province' => $this->shipping_province,
            'shipping_postal_code' => $this->shipping_postal_code,
            'notes' => $this->notes,
            'items'        => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
