<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource untuk format response kategori produk.
 * 
 * Mengembalikan data kategori produk dengan struktur terstandarisasi.
 */
class ProductCategoryResource extends JsonResource
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
                'id'   => $this->id,
                'name' => $this->name,
                'slug' => $this->slug,
                // Tambahkan field lain sesuai kebutuhan
            ],
        ];
    }
}
