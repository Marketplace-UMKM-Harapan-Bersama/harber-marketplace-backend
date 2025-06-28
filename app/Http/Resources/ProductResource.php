<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public $status;
    public $message;

    public function __construct($resource, $status = 200, $message = 'Success')
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => $this->status,
            'message' => $this->message,
            'data' => [
                'id'          => $this->id,
                'name'        => $this->name,
                'slug'        => $this->slug,
                'price'       => $this->price,
                'description' => $this->description,
                'category'    => new ProductCategoryResource($this->whenLoaded('category')),
                // Tambahkan field lain sesuai kebutuhan
            ]
        ];
    }
}
