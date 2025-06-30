<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
     protected $fillable = [
        'user_id', 'shop_name', 'shop_url', 'shop_description',
        'client_id', 'client_secret', 'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(MarketplaceUser::class, 'user_id');
    }
}
