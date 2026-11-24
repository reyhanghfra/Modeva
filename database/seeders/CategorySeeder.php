<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Menjalankan database seeder kategori produk secara idempotent.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pakaian',
                'description' => 'Koleksi busana wanita dan pria eksklusif, dress linen, blazer, kemeja, dan setelan kasual premium.',
                'is_active' => true,
            ],
            [
                'name' => 'Sepatu',
                'description' => 'Alas kaki bergaya minimalis, sneakers kulit putih, sandal hak blok, dan loafers elegan.',
                'is_active' => true,
            ],
            [
                'name' => 'Tas',
                'description' => 'Tas tangan kulit premium, shoulder bag, tote bag kanvas pantai, dan dompet fungsional.',
                'is_active' => true,
            ],
            [
                'name' => 'Aksesoris',
                'description' => 'Pelengkap gaya busana: ikat pinggang kulit, scarf sutra, topi fedora, dan kacamata hitam.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['name' => $data['name']],
                [
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => $data['description'],
                    'is_active' => $data['is_active'],
                ]
            );
        }
    }
}
