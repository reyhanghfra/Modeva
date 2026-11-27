<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    /**
     * Menjalankan seeder contoh item keranjang untuk pelanggan demo secara idempotent.
     */
    public function run(): void
    {
        $customer = User::where('email', 'pelanggan@modeva.test')->where('role', 'pelanggan')->first();

        if (! $customer) {
            return;
        }

        $skus = ['MDV-PAK-001', 'MDV-SPT-001'];

        foreach ($skus as $index => $sku) {
            $product = Product::where('sku', $sku)->where('is_active', true)->first();
            $variant = $product?->variants()->where('stock', '>', 0)->first();

            if (! $variant) {
                continue;
            }

            CartItem::updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'product_variant_id' => $variant->id,
                ],
                [
                    'quantity' => min($index + 1, $variant->stock),
                ]
            );
        }
    }
}
