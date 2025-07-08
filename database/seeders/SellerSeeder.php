<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\MarketplaceUser;
use App\Models\Seller;
use Illuminate\Support\Str;

class SellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sellers = [
            [
                'name' => 'Andi Toko',
                'email' => 'andi@example.com',
                'phone_number' => '081234567890',
                'address' => 'Jl. Melati No. 1',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'shop_name' => 'Toko Andi',
            ],
            [
                'name' => 'Budi Shop',
                'email' => 'budi@example.com',
                'phone_number' => '081234567891',
                'address' => 'Jl. Mawar No. 2',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'postal_code' => '40123',
                'shop_name' => 'Budi Electronics',
            ],
            [
                'name' => 'Citra Store',
                'email' => 'citra@example.com',
                'phone_number' => '081234567892',
                'address' => 'Jl. Anggrek No. 3',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'postal_code' => '60234',
                'shop_name' => 'Citra Fashion',
            ],
        ];

        foreach ($sellers as $data) {
            $user = MarketplaceUser::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => \Hash::make('password'), // default password
                'phone_number' => $data['phone_number'],
                'address' => $data['address'],
                'city' => $data['city'],
                'province' => $data['province'],
                'postal_code' => $data['postal_code'],
                'role' => 'seller',
                'email_verified_at' => now(),
            ]);

            Seller::create([
                'user_id' => $user->id,
                'shop_name' => $data['shop_name'],
                'shop_url' => Str::slug($data['shop_name']),
                'shop_description' => 'Ini adalah deskripsi dari ' . $data['shop_name'],
                'client_id' => 'client_' . Str::random(10),
                'client_secret' => Str::random(32),
                'is_active' => true,
            ]);
        }
    }
}
