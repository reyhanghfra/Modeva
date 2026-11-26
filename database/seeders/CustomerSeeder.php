<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Menjalankan seeder profil pelanggan secara idempotent.
     */
    public function run(): void
    {
        $profiles = [
            'pelanggan@modeva.test' => [
                'phone' => '081234567890',
                'address' => 'Jl. Kemang Raya No. 18, Jakarta Selatan',
            ],
            'nonaktif@modeva.test' => [
                'phone' => '081298765432',
                'address' => 'Jl. Melati No. 5, Depok',
            ],
            'anindya@modeva.test' => [
                'phone' => '081345678901',
                'address' => 'Jl. Dago Pakar Barat No. 42, Bandung',
            ],
            'maya@modeva.test' => [
                'phone' => '081567890123',
                'address' => 'Jl. Darmo Permai Selatan No. 12, Surabaya',
            ],
            'rizky@modeva.test' => [
                'phone' => '081789012345',
                'address' => 'Jl. Malioboro No. 88, Yogyakarta',
            ],
            'siti@modeva.test' => [
                'phone' => '081890123456',
                'address' => 'Jl. Sudirman No. 105, Jakarta Pusat',
            ],
            'bagas@modeva.test' => [
                'phone' => '082112233445',
                'address' => 'Jl. Pandanaran No. 27, Semarang',
            ],
            'dewi@modeva.test' => [
                'phone' => '082233445566',
                'address' => 'Jl. Raya Puputan No. 64, Denpasar',
            ],
            'eko@modeva.test' => [
                'phone' => '085211223344',
                'address' => 'Jl. Ijen Boulevard No. 9, Malang',
            ],
            'fajar@modeva.test' => [
                'phone' => '085344556677',
                'address' => 'Jl. Ahmad Yani No. 73, Bekasi',
            ],
            'gita@modeva.test' => [
                'phone' => '087812345678',
                'address' => 'Jl. Pajajaran No. 31, Bogor',
            ],
            'hendra@modeva.test' => [
                'phone' => '089612345678',
                'address' => 'Jl. Sisingamangaraja No. 15, Medan',
            ],
        ];

        $customers = User::where('role', 'pelanggan')->get();

        foreach ($customers as $index => $user) {
            $profile = $profiles[$user->email] ?? [
                'phone' => '0812' . str_pad((string) ($index + 10000000), 8, '0', STR_PAD_LEFT),
                'address' => 'Jl. Modeva No. ' . ($index + 1) . ', Jakarta',
            ];

            Customer::updateOrCreate(
                ['user_id' => $user->id],
                $profile
            );
        }
    }
}
