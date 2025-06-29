<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk format response produk.
 * 
 * Mengembalikan data produk beserta kategori (jika relasi dimuat) dengan struktur terstandarisasi.
 */
class ProductResource extends JsonResource
{
    public $status;
    public $message;

    /**
     * Konstruktor resource.
     *
     * @param mixed $resource
     * @param int $status
     * @param string $message
     */
    public function __construct($resource, $status = 200, $message = 'Success')
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Transformasi resource ke array.
     *
     * @param  \Illuminate\Http\Request  $request
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
