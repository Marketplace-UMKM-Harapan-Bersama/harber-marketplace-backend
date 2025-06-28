<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
     protected $fillable = [
        'seller_id', 'seller_product_id', 'name', 'description',
        'price', 'stock', 'category_id', 'last_synced_at'
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
