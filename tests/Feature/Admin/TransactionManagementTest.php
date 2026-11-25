<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\PaymentSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;
    protected User $pelanggan1;
    protected User $pelanggan2;
    protected Order $order1;
    protected Order $order2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Modeva',
            'email' => 'admin@modeva.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugas = User::factory()->create([
            'name' => 'Petugas Modeva',
            'email' => 'petugas@modeva.test',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->pelanggan1 = User::factory()->create([
            'name' => 'Anindya Putri',
            'email' => 'anindya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        Customer::create([
            'user_id' => $this->pelanggan1->id,
            'phone' => '081345678901',
            'address' => 'Jl. Dago Pakar Barat No. 42, Bandung',
        ]);

        $this->pelanggan2 = User::factory()->create([
            'name' => 'Maya Lestari',
            'email' => 'maya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $this->order1 = Order::create([
            'order_number' => 'MDV-250611',
            'user_id' => $this->pelanggan1->id,
            'shipping_address' => 'Jl. Dago Pakar Barat No. 42, Bandung',
            'total' => 1450000,
            'status' => Order::STATUS_SELESAI,
        ]);

        OrderItem::create([
            'order_id' => $this->order1->id,
            'product_id' => null,
            'product_name' => 'Sneakers Kulit Putih Pagi',
            'variant' => 'Putih · 38',
            'quantity' => 1,
            'price' => 850000,
            'subtotal' => 850000,
        ]);

        OrderItem::create([
            'order_id' => $this->order1->id,
            'product_id' => null,
            'product_name' => 'Cardigan Rajut Anak Hangat',
            'variant' => 'Oatmeal · 10 Tahun',
            'quantity' => 2,
            'price' => 300000,
            'subtotal' => 600000,
        ]);

        Payment::create([
            'order_id' => $this->order1->id,
            'method' => 'transfer',
            'bank_name' => 'BCA',
            'sender_name' => 'Anindya Putri',
            'amount' => 1450000,
            'status' => Payment::STATUS_TERVERIFIKASI,
            'uploaded_at' => now()->subDay(),
            'verified_by' => $this->admin->id,
            'verified_at' => now(),
        ]);

        $this->order2 = Order::create([
            'order_number' => 'MDV-250612',
            'user_id' => $this->pelanggan2->id,
            'shipping_address' => 'Jl. Darmo Permai Selatan No. 12, Surabaya',
            'total' => 550000,
            'status' => Order::STATUS_VERIFIKASI_PEMBAYARAN,
        ]);

        Payment::create([
            'order_id' => $this->order2->id,
            'method' => 'transfer',
            'bank_name' => 'Mandiri',
            'sender_name' => 'Maya Lestari',
            'amount' => 550000,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'uploaded_at' => now(),
        ]);
    }

    public function test_admin_can_view_all_transactions_with_required_columns(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Transaksi');

        // Transaksi 1
        $response->assertSee('MDV-250611');
        $response->assertSee('Anindya Putri');
        $response->assertSee($this->order1->created_at->format('d M Y, H:i'));
        $response->assertSee('Rp1.450.000');
        $response->assertSee('Transfer (BCA)');
        $response->assertSee('Selesai');

        // Transaksi 2
        $response->assertSee('MDV-250612');
        $response->assertSee('Maya Lestari');
        $response->assertSee('Rp550.000');
        $response->assertSee('Transfer (Mandiri)');
        $response->assertSee('Verifikasi Pembayaran');

        // Tombol lihat detail transaksi tersedia
        $response->assertSee(route('admin.transactions.show', $this->order1));
        $response->assertSee(route('admin.transactions.show', $this->order2));
    }

    public function test_admin_can_view_transaction_detail_with_products_shipping_and_payment(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.transactions.show', $this->order1));

        $response->assertStatus(200);
        $response->assertSee('Detail Transaksi');
        $response->assertSee('MDV-250611');

        // Informasi produk, jumlah, harga, subtotal
        $response->assertSee('Sneakers Kulit Putih Pagi');
        $response->assertSee('Putih · 38');
        $response->assertSee('Rp850.000');
        $response->assertSee('Cardigan Rajut Anak Hangat');
        $response->assertSee('Rp300.000');
        $response->assertSee('Rp600.000');
        $response->assertSee('Rp1.450.000');

        // Alamat pengiriman & penerima
        $response->assertSee('Anindya Putri');
        $response->assertSee('081345678901');
        $response->assertSee('Jl. Dago Pakar Barat No. 42, Bandung');

        // Informasi pembayaran
        $response->assertSee('BCA');
        $response->assertSee('Terverifikasi');
        $response->assertSee('Admin Modeva');
    }

    public function test_non_admin_cannot_access_admin_transactions(): void
    {
        $this->actingAs($this->petugas)->get(route('admin.transactions.index'))->assertStatus(403);
        $this->actingAs($this->petugas)->get(route('admin.transactions.show', $this->order1))->assertStatus(403);

        $this->actingAs($this->pelanggan1)->get(route('admin.transactions.index'))->assertStatus(403);
        $this->actingAs($this->pelanggan1)->get(route('admin.transactions.show', $this->order1))->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_from_admin_transactions(): void
    {
        $this->get(route('admin.transactions.index'))->assertRedirect(route('login'));
        $this->get(route('admin.transactions.show', $this->order1))->assertRedirect(route('login'));
    }

    public function test_order_and_payment_seeders_populate_real_database_transactions_idempotently(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(CustomerSeeder::class);
        $this->seed(OrderSeeder::class);
        $this->seed(OrderSeeder::class);
        $this->seed(PaymentSeeder::class);

        $this->assertDatabaseHas('orders', ['order_number' => 'MDV-250607']);
        $this->assertDatabaseHas('orders', ['order_number' => 'MDV-250611']);
        $this->assertGreaterThanOrEqual(5, OrderItem::count());

        $seededOrder = Order::where('order_number', 'MDV-250608')->firstOrFail();
        $this->assertNotNull($seededOrder->latestPayment);
    }

    public function test_admin_can_search_transactions_by_order_number_or_customer_name(): void
    {
        // Cari berdasarkan nomor transaksi
        $byNumber = $this->actingAs($this->admin)->get(route('admin.transactions.index', [
            'search' => 'MDV-250611',
        ]));

        $byNumber->assertStatus(200);
        $byNumber->assertSee('MDV-250611');
        $byNumber->assertSee('Anindya Putri');
        $byNumber->assertDontSee('MDV-250612');

        // Cari berdasarkan nama customer
        $byCustomer = $this->actingAs($this->admin)->get(route('admin.transactions.index', [
            'search' => 'Maya',
        ]));

        $byCustomer->assertStatus(200);
        $byCustomer->assertSee('MDV-250612');
        $byCustomer->assertSee('Maya Lestari');
        $byCustomer->assertDontSee('MDV-250611');
    }

    public function test_admin_can_filter_transactions_by_status(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.transactions.index', [
            'status' => Order::STATUS_SELESAI,
        ]));

        $response->assertStatus(200);
        $response->assertSee('MDV-250611');
        $response->assertDontSee('MDV-250612');
    }

    public function test_admin_can_filter_transactions_by_date_range(): void
    {
        $this->order1->forceFill(['created_at' => '2026-11-10 10:00:00'])->save();
        $this->order2->forceFill(['created_at' => '2026-11-20 15:30:00'])->save();

        $response = $this->actingAs($this->admin)->get(route('admin.transactions.index', [
            'start_date' => '2026-11-15',
            'end_date' => '2026-11-25',
        ]));

        $response->assertStatus(200);
        $response->assertSee('MDV-250612');
        $response->assertDontSee('MDV-250611');
    }

    public function test_transactions_pagination_preserves_search_and_filters_across_pages(): void
    {
        $otherCustomer = User::factory()->create([
            'name' => 'Pelanggan Lain',
            'email' => 'lain@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        // Buat 12 transaksi cocok dengan filter (Anindya + status selesai + tanggal dalam rentang)
        for ($i = 1; $i <= 12; $i++) {
            $order = Order::create([
                'order_number' => 'MDV-FILTER-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'user_id' => $this->pelanggan1->id,
                'shipping_address' => 'Bandung',
                'total' => 200000,
                'status' => Order::STATUS_SELESAI,
            ]);
            $order->forceFill(['created_at' => '2026-11-18 12:00:00'])->save();
        }

        // Buat 1 transaksi yang tidak cocok (beda customer & status)
        $excluded = Order::create([
            'order_number' => 'MDV-EXCLUDED-99',
            'user_id' => $otherCustomer->id,
            'shipping_address' => 'Jakarta',
            'total' => 300000,
            'status' => Order::STATUS_DIPROSES,
        ]);
        $excluded->forceFill(['created_at' => '2026-11-18 12:00:00'])->save();

        $queryParams = [
            'search' => 'Anindya',
            'status' => Order::STATUS_SELESAI,
            'start_date' => '2026-11-18',
            'end_date' => '2026-11-19',
        ];

        // Halaman 1 (10 dari 12 transaksi terfilter)
        $page1 = $this->actingAs($this->admin)->get(route('admin.transactions.index', $queryParams));
        $page1->assertStatus(200);
        $page1->assertSee('Menampilkan');
        $page1->assertSee('MDV-FILTER-12');
        $page1->assertDontSee('MDV-EXCLUDED-99');
        $page1->assertSee('search=Anindya');
        $page1->assertSee('status=selesai');
        $page1->assertSee('start_date=2026-11-18');
        $page1->assertSee('end_date=2026-11-19');

        // Halaman 2 (sisa 2 transaksi terfilter tetap mempertahankan query)
        $page2 = $this->actingAs($this->admin)->get(route('admin.transactions.index', array_merge($queryParams, ['page' => 2])));
        $page2->assertStatus(200);
        $page2->assertSee('MDV-FILTER-02');
        $page2->assertSee('MDV-FILTER-01');
        $page2->assertDontSee('MDV-FILTER-12');
        $page2->assertDontSee('MDV-EXCLUDED-99');
    }
}
