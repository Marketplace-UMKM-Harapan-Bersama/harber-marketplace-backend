<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'seller_id',
        'parent_id',
    ];

    // Relasi ke Seller
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    // Relasi ke parent category
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    // Relasi ke subkategori (child)
    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    // Relasi ke produk (jika nanti produk punya kategori)
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
