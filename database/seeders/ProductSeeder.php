<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Product;
use App\Models\Seller;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan sudah ada seller dan category
        $sellers = Seller::all();
        $categories = ProductCategory::all();

        if ($sellers->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Harap isi sellers dan product_categories terlebih dahulu.');
            return;
        }

        foreach (range(1, 10) as $i) {
            $seller = $sellers->random();
            $category = $categories->random();

            Product::create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'seller_product_id' => 'SP-' . strtoupper(Str::random(6)),
                'name' => 'Sample Product ' . $i,
                'description' => 'This is a sample product description for product ' . $i,
                'price' => rand(10000, 100000) / 100,
                'stock' => rand(10, 100),
                'sku' => 'SKU-' . strtoupper(Str::random(8)),
                'image_url' => 'https://via.placeholder.com/150',
                'weight' => rand(1, 500) / 10,
                'is_active' => true,
                'last_synced_at' => now(),
            ]);
        }
    }
}
