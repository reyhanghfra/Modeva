<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@modeva.test', 'role' => 'admin'],
            ['name' => 'Petugas', 'email' => 'petugas@modeva.test', 'role' => 'petugas'],
            ['name' => 'Pelanggan', 'email' => 'pelanggan@modeva.test', 'role' => 'pelanggan'],
        ];

        foreach ($users as $user) {
            User::create([...$user, 'password' => 'password']);
        }
    }
}