<?php

namespace Tests\Feature\Petugas;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;
    protected User $pelanggan;
    protected Order $orderVerifikasi;
    protected Order $orderDiproses;
    protected Order $orderMenunggu;
    protected Order $orderDikirim;
    protected Order $orderSelesai;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Modeva',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugas = User::factory()->create([
            'name' => 'Petugas Modeva',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->pelanggan = User::factory()->create([
            'name' => 'Anindya Putri',
            'email' => 'anindya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        Customer::create([
            'user_id' => $this->pelanggan->id,
            'phone' => '081345678901',
            'address' => 'Jl. Dago Pakar Barat No. 42, Bandung',
        ]);

        // Transaksi dalam kewenangan petugas
        $this->orderVerifikasi = Order::create([
            'order_number' => 'MDV-PETUGAS-01',
            'user_id' => $this->pelanggan->id,
            'shipping_address' => 'Jl. Dago Pakar Barat No. 42, Bandung',
            'shipping_service' => 'J&T Express',
            'total' => 690000,
            'status' => Order::STATUS_VERIFIKASI_PEMBAYARAN,
        ]);

        OrderItem::create([
            'order_id' => $this->orderVerifikasi->id,
            'product_id' => null,
            'product_name' => 'Dress Midi Linen Kopi',
            'variant' => 'Cokelat Kopi · M',
            'quantity' => 1,
            'price' => 690000,
            'subtotal' => 690000,
        ]);

        Payment::create([
            'order_id' => $this->orderVerifikasi->id,
            'method' => 'transfer',
            'bank_name' => 'BCA',
            'sender_name' => 'Anindya Putri',
            'amount' => 690000,
            'proof_path' => 'payment-proofs/contoh-bukti.jpg',
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'uploaded_at' => now(),
        ]);

        $this->orderDiproses = Order::create([
            'order_number' => 'MDV-PETUGAS-02',
            'user_id' => $this->pelanggan->id,
            'shipping_address' => 'Jl. Dago Pakar Barat No. 42, Bandung',
            'total' => 420000,
            'status' => Order::STATUS_DIPROSES,
        ]);

        // Transaksi di luar kewenangan daftar tindakan petugas
        $this->orderMenunggu = Order::create([
            'order_number' => 'MDV-NONPETUGAS-WAIT',
            'user_id' => $this->pelanggan->id,
            'total' => 250000,
            'status' => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ]);

        $this->orderDikirim = Order::create([
            'order_number' => 'MDV-NONPETUGAS-SHIP',
            'user_id' => $this->pelanggan->id,
            'total' => 350000,
            'status' => Order::STATUS_DIKIRIM,
        ]);

        $this->orderSelesai = Order::create([
            'order_number' => 'MDV-NONPETUGAS-DONE',
            'user_id' => $this->pelanggan->id,
            'total' => 500000,
            'status' => Order::STATUS_SELESAI,
        ]);
    }

    public function test_petugas_can_view_only_transactions_that_need_handling(): void
    {
        $response = $this->actingAs($this->petugas)->get(route('petugas.transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Transaksi');

        // Transaksi yang perlu ditangani petugas tampil
        $response->assertSee('MDV-PETUGAS-01');
        $response->assertSee('Anindya Putri');
        $response->assertSee($this->orderVerifikasi->created_at->format('d M Y, H:i'));
        $response->assertSee('Rp690.000');
        $response->assertSee('Verifikasi Pembayaran');
        $response->assertSee('Perlu verifikasi pembayaran');

        $response->assertSee('MDV-PETUGAS-02');
        $response->assertSee('Rp420.000');
        $response->assertSee('Diproses');
        $response->assertSee('Perlu pengemasan &amp; pengiriman', false);

        // Tombol detail pesanan tersedia
        $response->assertSee(route('petugas.transactions.show', $this->orderVerifikasi));
        $response->assertSee(route('petugas.transactions.show', $this->orderDiproses));

        // Transaksi di luar kewenangan petugas tidak muncul di daftar
        $response->assertDontSee('MDV-NONPETUGAS-WAIT');
        $response->assertDontSee('MDV-NONPETUGAS-SHIP');
        $response->assertDontSee('MDV-NONPETUGAS-DONE');
    }

    public function test_petugas_can_view_detail_of_handled_order(): void
    {
        $response = $this->actingAs($this->petugas)->get(route('petugas.transactions.show', $this->orderVerifikasi));

        $response->assertStatus(200);
        $response->assertSee('Detail Pesanan');
        $response->assertSee('MDV-PETUGAS-01');
        $response->assertSee($this->orderVerifikasi->created_at->format('d M Y, H:i'));

        // Data pelanggan, alamat pengiriman & pembayaran, jasa pengiriman
        $response->assertSee('Anindya Putri');
        $response->assertSee('anindya@modeva.test');
        $response->assertSee('081345678901');
        $response->assertSee('Jl. Dago Pakar Barat No. 42, Bandung');
        $response->assertSee('J&amp;T Express', false);

        // Produk, warna, ukuran, jumlah, harga, subtotal, total pembayaran
        $response->assertSee('Dress Midi Linen Kopi');
        $response->assertSee('Cokelat Kopi');
        $response->assertSee('M');
        $response->assertSee('Rp690.000');

        // Informasi pembayaran, bukti transfer, status pesanan saat ini (read-only)
        $response->assertSee('BCA');
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Verifikasi Pembayaran');
        $response->assertSee('Buka bukti ukuran penuh di tab baru');
        $response->assertDontSee('Tolak Pembayaran');

        // Pesanan tanpa bukti transfer menampilkan pesan kosong yang rapi
        $responseDiproses = $this->actingAs($this->petugas)->get(route('petugas.transactions.show', $this->orderDiproses));
        $responseDiproses->assertStatus(200);
        $responseDiproses->assertSee('Bukti transfer belum tersedia');
    }

    public function test_petugas_cannot_view_detail_of_order_outside_petugas_authority(): void
    {
        $this->actingAs($this->petugas)
            ->get(route('petugas.transactions.show', $this->orderMenunggu))
            ->assertStatus(403);

        $this->actingAs($this->petugas)
            ->get(route('petugas.transactions.show', $this->orderDikirim))
            ->assertStatus(403);

        $this->actingAs($this->petugas)
            ->get(route('petugas.transactions.show', $this->orderSelesai))
            ->assertStatus(403);
    }

    public function test_non_petugas_cannot_access_petugas_transactions(): void
    {
        $this->actingAs($this->admin)->get(route('petugas.transactions.index'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('petugas.transactions.show', $this->orderVerifikasi))->assertStatus(403);

        $this->actingAs($this->pelanggan)->get(route('petugas.transactions.index'))->assertStatus(403);
        $this->actingAs($this->pelanggan)->get(route('petugas.transactions.show', $this->orderVerifikasi))->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_from_petugas_transactions(): void
    {
        $this->get(route('petugas.transactions.index'))->assertRedirect(route('login'));
        $this->get(route('petugas.transactions.show', $this->orderVerifikasi))->assertRedirect(route('login'));
    }
}
