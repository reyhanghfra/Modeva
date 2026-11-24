<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_with_ringkasan_username_and_logout_button(): void
    {
        $admin = User::factory()->create([
            'name' => 'Budi Pemilik',
            'email' => 'admin.budi@modeva.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Ringkasan');
        $response->assertSee('Performa toko MODEVA secara keseluruhan');
        $response->assertSee('Budi Pemilik');
        $response->assertSee('Pemilik / Admin');
        $response->assertSee('Keluar');

        // Memastikan stat cards muncul
        $response->assertSee('Total Pendapatan');
        $response->assertSee('Rp6.982.000');
        $response->assertSee('+12% dari bulan lalu');
        $response->assertSee('Total Pesanan');
        $response->assertSee('10');
        $response->assertSee('Total Produk');
        $response->assertSee('12');
        $response->assertSee('Laba Bersih');
        $response->assertSee('Rp3.462.000');

        // Memastikan panel performa penjualan & stok menipis
        $response->assertSee('Performa Penjualan');
        $response->assertSee('7 hari terakhir');
        $response->assertSee('Stok Menipis');
        $response->assertSee('Blazer Wol Oversize Malam');
        $response->assertSee('Kelola inventori');

        // Memastikan panel transaksi terbaru & produk terlaris
        $response->assertSee('Transaksi Terbaru');
        $response->assertSee('Anindya Putri');
        $response->assertSee('Lihat semua');
        $response->assertSee('Produk Terlaris');
        $response->assertSee('Sneakers Kulit Putih Pagi');
        $response->assertSee('212 terjual');
    }

    public function test_petugas_cannot_access_admin_dashboard_and_gets_403(): void
    {
        $petugas = User::factory()->create([
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($petugas)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_pelanggan_cannot_access_admin_dashboard_and_gets_403(): void
    {
        $pelanggan = User::factory()->create([
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($pelanggan)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guest_visiting_admin_dashboard_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }
}
