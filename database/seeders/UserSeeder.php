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
            [
                'name' => 'Admin Modeva',
                'email' => 'admin@modeva.test',
                'role' => 'admin',
                'is_active' => true,
                'password' => 'password',
            ],
            [
                'name' => 'Petugas Modeva',
                'email' => 'petugas@modeva.test',
                'role' => 'petugas',
                'is_active' => true,
                'password' => 'password',
            ],
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
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
