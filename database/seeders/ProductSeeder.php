<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $categories = Category::query()->get()->keyBy('name');
        $products = [
            [
                'category' => 'Pakaian',
                'sku' => 'MDV-PAK-001',
                'name' => 'Kemeja Linen Oversize',
                'brand' => 'Kala Studio',
                'description' => 'Kemeja linen berpotongan santai untuk tampilan harian yang rapi.',
                'price' => 425000,
                'image' => 'products/kemeja-linen-oversize.jpg',
                'variants' => [
                    ['color' => 'Taupe', 'size' => 'S', 'stock' => 12],
                    ['color' => 'Taupe', 'size' => 'M', 'stock' => 8],
                    ['color' => 'Krem', 'size' => 'M', 'stock' => 4],
                    ['color' => 'Hitam', 'size' => 'L', 'stock' => 0],
                ],
            ],
            [
                'category' => 'Pakaian',
                'sku' => 'MDV-PAK-002',
                'name' => 'Dress Midi Linen',
                'brand' => 'Arunika',
                'description' => 'Dress midi berbahan linen ringan dengan siluet feminin.',
                'price' => 695000,
                'image' => 'products/dress-midi-linen.jpg',
                'variants' => [
                    ['color' => 'Camel', 'size' => 'S', 'stock' => 6],
                    ['color' => 'Camel', 'size' => 'M', 'stock' => 3],
                    ['color' => 'Krem', 'size' => 'L', 'stock' => 7],
                ],
            ],
            [
                'category' => 'Sepatu',
                'sku' => 'MDV-SPT-001',
                'name' => 'Loafer Kulit Minimal',
                'brand' => 'Lumen',
                'description' => 'Loafer kulit dengan desain minimalis dan sol yang nyaman.',
                'price' => 890000,
                'image' => 'products/loafer-kulit-minimal.jpg',
                'variants' => [
                    ['color' => 'Hitam', 'size' => '37', 'stock' => 5],
                    ['color' => 'Hitam', 'size' => '38', 'stock' => 9],
                    ['color' => 'Camel', 'size' => '39', 'stock' => 2],
                    ['color' => 'Camel', 'size' => '40', 'stock' => 4],
                ],
            ],
            [
                'category' => 'Sepatu',
                'sku' => 'MDV-SPT-002',
                'name' => 'Sandal Kulit Silang',
                'brand' => 'Sora',
                'description' => 'Sandal kulit dengan tali silang dan warna netral serbaguna.',
                'price' => 645000,
                'image' => 'products/sandal-kulit-silang.jpg',
                'variants' => [
                    ['color' => 'Camel', 'size' => '37', 'stock' => 11],
                    ['color' => 'Camel', 'size' => '38', 'stock' => 6],
                    ['color' => 'Hitam', 'size' => '39', 'stock' => 3],
                    ['color' => 'Hitam', 'size' => '40', 'stock' => 0],
                ],
            ],
            [
                'category' => 'Tas',
                'sku' => 'MDV-TAS-001',
                'name' => 'Tote Kanvas Harian',
                'brand' => 'Nusantara Atelier',
                'description' => 'Tote bag kanvas berkapasitas luas dengan detail sederhana.',
                'price' => 325000,
                'image' => 'products/tote-kanvas-harian.jpg',
                'variants' => [
                    ['color' => 'Krem', 'size' => 'Satu Ukuran', 'stock' => 14],
                    ['color' => 'Arang', 'size' => 'Satu Ukuran', 'stock' => 5],
                ],
            ],
            [
                'category' => 'Tas',
                'sku' => 'MDV-TAS-002',
                'name' => 'Shoulder Bag Struktur',
                'brand' => 'Kala Studio',
                'description' => 'Shoulder bag berstruktur dengan ruang utama yang praktis.',
                'price' => 780000,
                'image' => 'products/shoulder-bag-struktur.jpg',
                'variants' => [
                    ['color' => 'Hitam', 'size' => 'Satu Ukuran', 'stock' => 4],
                    ['color' => 'Camel', 'size' => 'Satu Ukuran', 'stock' => 8],
                    ['color' => 'Taupe', 'size' => 'Satu Ukuran', 'stock' => 2],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::query()->updateOrCreate(
                ['sku' => $productData['sku']],
                [
                    'category_id' => $categories->get($productData['category'])->id,
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'brand' => $productData['brand'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'image' => $productData['image'],
                    'is_active' => true,
                ]
            );

            foreach ($productData['variants'] as $variantData) {
                $product->variants()->updateOrCreate(
                    [
                        'color' => $variantData['color'],
                        'size' => $variantData['size'],
                    ],
                    [
                        'stock' => $variantData['stock'],
                        'low_stock_threshold' => 5,
                    ]
                );
            }
        }
    }
}
