<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Menjalankan database seeder secara idempotent.
     */
    public function run(): void
    {
        $users = [
            // Admin
            [
                'name' => 'Admin Modeva',
                'email' => 'admin@modeva.test',
                'role' => 'admin',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Admin Operasional',
                'email' => 'admin2@modeva.test',
                'role' => 'admin',
                'is_active' => true,
                'password' => 'password',
            ],

            // Petugas
            [
                'name' => 'Petugas Modeva',
                'email' => 'petugas@modeva.test',
                'role' => 'petugas',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Petugas Verifikasi',
                'email' => 'petugas2@modeva.test',
                'role' => 'petugas',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Petugas Inventori',
                'email' => 'petugas3@modeva.test',
                'role' => 'petugas',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Petugas Nonaktif',
                'email' => 'petugas.nonaktif@modeva.test',
                'role' => 'petugas',
                'is_active' => false,
                'password' => 'password',
            ],

            // Pelanggan
            [
                'name' => 'Pelanggan Modeva',
                'email' => 'pelanggan@modeva.test',
                'role' => 'pelanggan',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Pelanggan Nonaktif',
                'email' => 'nonaktif@modeva.test',
                'role' => 'pelanggan',
                'is_active' => false,
                'password' => 'password',
            ],
            [
                'name' => 'Anindya Putri',
                'email' => 'anindya@modeva.test',
                'role' => 'pelanggan',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Maya Lestari',
                'email' => 'maya@modeva.test',
                'role' => 'pelanggan',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Rizky Pratama',
                'email' => 'rizky@modeva.test',
                'role' => 'pelanggan',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@modeva.test',
                'role' => 'pelanggan',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Bagas Wicaksono',
                'email' => 'bagas@modeva.test',
                'role' => 'pelanggan',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@modeva.test',
                'role' => 'pelanggan',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko@modeva.test',
                'role' => 'pelanggan',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Fajar Nugraha',
                'email' => 'fajar@modeva.test',
                'role' => 'pelanggan',
                'is_active' => false,
                'password' => 'password',
            ],
            [
                'name' => 'Gita Savitri',
                'email' => 'gita@modeva.test',
                'role' => 'pelanggan',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Hendra Gunawan',
                'email' => 'hendra@modeva.test',
                'role' => 'pelanggan',
                'is_active' => true,
                'password' => 'password',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
