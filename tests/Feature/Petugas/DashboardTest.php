<?php

namespace Tests\Feature\Petugas;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_view_operational_dashboard_with_real_database_data(): void
    {
        $petugas = User::factory()->create([
            'name' => 'Sari Petugas',
            'email' => 'sari.petugas@modeva.test',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $pelanggan = User::factory()->create([
            'name' => 'Anindya Putri',
            'email' => 'anindya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        // 2 menunggu_pembayaran, 1 verifikasi_pembayaran, 1 diproses, 1 dikirim, 2 selesai
        Order::create([
            'order_number' => 'MDV-WAIT-01',
            'user_id' => $pelanggan->id,
            'total' => 250000,
            'status' => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ]);
        Order::create([
            'order_number' => 'MDV-WAIT-02',
            'user_id' => $pelanggan->id,
            'total' => 300000,
            'status' => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ]);

        $orderVerif = Order::create([
            'order_number' => 'MDV-VERIF-01',
            'user_id' => $pelanggan->id,
            'total' => 450000,
            'status' => Order::STATUS_VERIFIKASI_PEMBAYARAN,
        ]);
        Payment::create([
            'order_id' => $orderVerif->id,
            'method' => 'transfer',
            'bank_name' => 'BCA',
            'sender_name' => 'Anindya Putri',
            'amount' => 450000,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'uploaded_at' => now(),
        ]);

        Order::create([
            'order_number' => 'MDV-PROC-01',
            'user_id' => $pelanggan->id,
            'total' => 550000,
            'status' => Order::STATUS_DIPROSES,
        ]);

        Order::create([
            'order_number' => 'MDV-SHIP-01',
            'user_id' => $pelanggan->id,
            'total' => 650000,
            'status' => Order::STATUS_DIKIRIM,
        ]);

        Order::create([
            'order_number' => 'MDV-DONE-01',
            'user_id' => $pelanggan->id,
            'total' => 750000,
            'status' => Order::STATUS_SELESAI,
        ]);
        Order::create([
            'order_number' => 'MDV-DONE-02',
            'user_id' => $pelanggan->id,
            'total' => 850000,
            'status' => Order::STATUS_SELESAI,
        ]);

        $response = $this->actingAs($petugas)->get(route('petugas.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dasbor Operasional');
        $response->assertSee('Sari Petugas');
        $response->assertSee('Staf / Petugas');

        // Ringkasan 4 status pesanan (angka nyata dari DB)
        $response->assertSee('Menunggu Pembayaran');
        $response->assertSee('Sedang Diproses');
        $response->assertSee('Dikirim');
        $response->assertSee('Selesai');
        $response->assertViewHas('summary', [
            'menunggu_pembayaran' => 2,
            'diproses' => 1,
            'dikirim' => 1,
            'selesai' => 2,
        ]);

        // Transaksi membutuhkan tindakan petugas
        $response->assertSee('Transaksi Membutuhkan Tindakan');
        $response->assertSee('1 pembayaran menunggu verifikasi');
        $response->assertSee('1 pesanan siap diproses / dikirim');
        $response->assertSee('MDV-VERIF-01');
        $response->assertSee('Perlu verifikasi bukti pembayaran');
        $response->assertSee('MDV-PROC-01');
        $response->assertSee('Perlu pengemasan dan pengiriman');

        // Daftar pesanan terbaru & navigasi ke Data Transaksi
        $response->assertSee('Pesanan Terbaru');
        $response->assertSee('MDV-DONE-02');
        $response->assertSee('Data Transaksi');
        $response->assertSee(route('petugas.transactions.index'));
    }

    public function test_petugas_dashboard_displays_zero_counts_when_database_is_empty(): void
    {
        $petugas = User::factory()->create([
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($petugas)->get(route('petugas.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('summary', [
            'menunggu_pembayaran' => 0,
            'diproses' => 0,
            'dikirim' => 0,
            'selesai' => 0,
        ]);
        $response->assertSee('Semua transaksi sudah tertangani');
        $response->assertSee('Belum ada pesanan masuk');
    }

    public function test_only_active_petugas_can_access_petugas_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $pelanggan = User::factory()->create(['role' => 'pelanggan', 'is_active' => true]);
        $inactivePetugas = User::factory()->create(['role' => 'petugas', 'is_active' => false]);

        $this->actingAs($admin)->get(route('petugas.dashboard'))->assertStatus(403);
        $this->actingAs($pelanggan)->get(route('petugas.dashboard'))->assertStatus(403);

        $this->actingAs($inactivePetugas)->get(route('petugas.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->get(route('petugas.dashboard'))->assertRedirect(route('login'));
    }
}
