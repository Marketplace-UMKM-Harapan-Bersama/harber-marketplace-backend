<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ProductCategory;
use App\Models\Seller;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua seller (boleh kosong karena nullable)
        $sellers = Seller::all();

        foreach ($sellers as $seller) {
            // Buat kategori utama untuk setiap seller
            $parentCategory = ProductCategory::create([
                'seller_id' => $seller->id,
                'name' => 'Elektronik',
                'slug' => Str::slug('Elektronik'),
                'parent_id' => null,
            ]);

            // Tambahkan subkategori
            $subCategories = ['Handphone', 'Laptop', 'Aksesoris Elektronik'];
            foreach ($subCategories as $sub) {
                ProductCategory::create([
                    'seller_id' => $seller->id,
                    'name' => $sub,
                    'slug' => Str::slug($sub),
                    'parent_id' => $parentCategory->id,
                ]);
            }
        }

        // Kategori global tanpa seller (misalnya default/global category)
        $globalCategories = [
            'Fashion Pria',
            'Fashion Wanita',
            'Makanan & Minuman',
            'Peralatan Rumah Tangga'
        ];

        foreach ($globalCategories as $cat) {
            ProductCategory::create([
                'seller_id' => null,
                'name' => $cat,
                'slug' => Str::slug($cat),
                'parent_id' => null,
            ]);
        }
    }
}
