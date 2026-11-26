<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Admin\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(app(ReportService::class)->activityLogPath());
    }

    protected function tearDown(): void
    {
        File::delete(app(ReportService::class)->activityLogPath());

        parent::tearDown();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    /**
     * Buat pesanan selesai beserta satu item produk.
     */
    private function selesaiOrder(array $orderOverrides = [], array $itemOverrides = []): Order
    {
        $order = Order::factory()->create(array_merge([
            'status' => Order::STATUS_SELESAI,
            'total' => 500000,
        ], $orderOverrides));

        OrderItem::factory()->create(array_merge([
            'order_id' => $order->id,
            'product_name' => 'Produk A',
            'quantity' => 2,
            'price' => 250000,
            'subtotal' => 500000,
        ], $itemOverrides));

        return $order;
    }

    // ─── Akses & Otorisasi ────────────────────────────────────────────────────

    public function test_admin_dapat_mengakses_halaman_laporan(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Penjualan');
    }

    public function test_petugas_tidak_bisa_mengakses_halaman_laporan(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true]);

        $this->actingAs($petugas)->get(route('admin.reports.index'))->assertStatus(403);
    }

    public function test_pelanggan_tidak_bisa_mengakses_halaman_laporan(): void
    {
        $pelanggan = User::factory()->create(['role' => 'pelanggan', 'is_active' => true]);

        $this->actingAs($pelanggan)->get(route('admin.reports.index'))->assertStatus(403);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
    }

    // ─── Tampilan Komponen UI ─────────────────────────────────────────────────

    public function test_halaman_menampilkan_filter_periode_dan_tombol_terapkan(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertSee('Tanggal Mulai');
        $response->assertSee('Tanggal Akhir');
        $response->assertSee('Terapkan Filter');
    }

    public function test_halaman_menampilkan_semua_stat_cards(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertSee('Total Penjualan');
        $response->assertSee('Transaksi Selesai');
        $response->assertSee('Rata-rata per Pesanan');
        $response->assertSee('Estimasi Laba');
    }

    public function test_halaman_menampilkan_panel_grafik_dan_produk_terlaris(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertSee('Grafik Penjualan Bulanan');
        $response->assertSee('Produk Terlaris');
    }

    public function test_halaman_menampilkan_catatan_estimasi_laba(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertSee('Catatan: Estimasi Laba');
        $response->assertSee('HPP (Harga Pokok Penjualan)', false);
        $response->assertSee('40%');
    }

    // ─── Data Nyata dari Database ─────────────────────────────────────────────

    public function test_total_penjualan_dihitung_dari_pesanan_selesai_saja(): void
    {
        // Dua pesanan selesai: total 500.000 + 300.000 = 800.000
        $this->selesaiOrder(['total' => 500000]);
        $this->selesaiOrder(['total' => 300000]);

        // Satu pesanan menunggu — tidak boleh ikut dihitung
        Order::factory()->create(['status' => Order::STATUS_MENUNGGU_PEMBAYARAN, 'total' => 999999]);

        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Rp800.000');
        $response->assertDontSee('Rp999.999');
    }

    public function test_jumlah_transaksi_selesai_ditampilkan_dengan_benar(): void
    {
        $this->selesaiOrder();
        $this->selesaiOrder();
        Order::factory()->create(['status' => Order::STATUS_DIBATALKAN, 'total' => 100000]);

        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        // 2 transaksi selesai
        $response->assertSee('2 transaksi selesai');
    }

    public function test_produk_terlaris_diurutkan_berdasarkan_qty_terjual(): void
    {
        $orderA = Order::factory()->create(['status' => Order::STATUS_SELESAI, 'total' => 1000000]);
        OrderItem::factory()->create([
            'order_id' => $orderA->id,
            'product_name' => 'Sepatu Kulit',
            'quantity' => 10,
            'price' => 100000,
            'subtotal' => 1000000,
        ]);

        $orderB = Order::factory()->create(['status' => Order::STATUS_SELESAI, 'total' => 300000]);
        OrderItem::factory()->create([
            'order_id' => $orderB->id,
            'product_name' => 'Kaos Polos',
            'quantity' => 3,
            'price' => 100000,
            'subtotal' => 300000,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertSee('Sepatu Kulit');
        $response->assertSee('Kaos Polos');

        // Pastikan Sepatu Kulit (qty 10) muncul sebelum Kaos Polos (qty 3)
        $this->assertLessThan(
            strpos($response->getContent(), 'Kaos Polos'),
            strpos($response->getContent(), 'Sepatu Kulit'),
        );
    }

    public function test_filter_periode_memfilter_data_sesuai_tanggal(): void
    {
        // Pesanan lama (bulan lalu)
        $this->selesaiOrder([
            'total' => 999000,
            'created_at' => now()->subMonth(),
        ]);

        // Pesanan baru (hari ini)
        $this->selesaiOrder([
            'total' => 100000,
            'created_at' => now(),
        ]);

        $start = now()->startOfMonth()->format('Y-m-d');
        $end = now()->endOfMonth()->format('Y-m-d');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.index', ['start_date' => $start, 'end_date' => $end]));

        // Hanya yang bulan ini: Rp100.000
        $response->assertSee('Rp100.000');
        // Pesanan bulan lalu tidak masuk hitungan
        $response->assertDontSee('Rp999.000');
    }

    public function test_reset_filter_menampilkan_tombol_reset_saat_filter_aktif(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.index', ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']));

        $response->assertSee('Reset');
        $response->assertSee('Filter aktif');
    }

    public function test_halaman_tanpa_data_menampilkan_state_kosong(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Rp0');
        $response->assertSee('0 transaksi selesai');
    }

    public function test_grafik_harian_ditampilkan_saat_filter_periode_kurang_dari_62_hari(): void
    {
        $start = now()->subDays(7)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.index', ['start_date' => $start, 'end_date' => $end]));

        $response->assertSee('Grafik Penjualan Harian');
    }

    // ─── Ekspor Excel ─────────────────────────────────────────────────────────

    public function test_halaman_menampilkan_tombol_ekspor_excel_dengan_parameter_filter(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.index', ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']));

        $response->assertStatus(200);
        $response->assertSee('Ekspor Excel');
        $response->assertSee(
            route('admin.reports.export', ['start_date' => '2026-01-01', 'end_date' => '2026-01-31'])
        );
    }

    public function test_admin_dapat_mengekspor_laporan_excel_sesuai_periode_dan_estimasi_laba(): void
    {
        // Pesanan bulan lalu (tidak boleh masuk filter bulan ini)
        $this->selesaiOrder([
            'order_number' => 'MDV-OLD001',
            'total' => 999000,
            'created_at' => now()->subMonth(),
        ], [
            'product_name' => 'Produk Lama',
            'subtotal' => 999000,
        ]);

        // Pesanan selesai bulan ini
        $this->selesaiOrder([
            'order_number' => 'MDV-NEW001',
            'total' => 500000,
            'created_at' => now(),
        ], [
            'product_name' => 'Blazer Wol Modeva',
            'quantity' => 2,
            'price' => 250000,
            'subtotal' => 500000,
        ]);

        // Pesanan belum selesai bulan ini (tidak boleh dihitung)
        Order::factory()->create([
            'order_number' => 'MDV-PENDING',
            'status' => Order::STATUS_MENUNGGU_PEMBAYARAN,
            'total' => 750000,
            'created_at' => now(),
        ]);

        $start = now()->startOfMonth()->format('Y-m-d');
        $end = now()->endOfMonth()->format('Y-m-d');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.export', ['start_date' => $start, 'end_date' => $end]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $this->assertStringContainsString(
            "laporan-penjualan-{$start}-sd-{$end}.xls",
            (string) $response->headers->get('Content-Disposition')
        );

        $response->assertSee('LAPORAN PENJUALAN MODEVA');
        $response->assertSee('500000'); // Total penjualan
        $response->assertSee('200000'); // Estimasi laba 40% dari 500.000
        $response->assertSee('Blazer Wol Modeva');
        $response->assertSee('MDV-NEW001');
        $response->assertDontSee('MDV-OLD001');
        $response->assertDontSee('MDV-PENDING');
    }

    public function test_non_admin_dan_tamu_tidak_dapat_mengekspor_laporan_excel(): void
    {
        $this->get(route('admin.reports.export'))->assertRedirect(route('login'));

        $petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true]);
        $this->actingAs($petugas)->get(route('admin.reports.export'))->assertStatus(403);
    }

    // ─── Log Aktivitas Laporan ────────────────────────────────────────────────

    public function test_melihat_dan_memfilter_laporan_tercatat_di_log_aktivitas(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Log Aktivitas Laporan');
        $response->assertSee('Melihat laporan penjualan');
        $response->assertSee($admin->email);

        $filtered = $this->actingAs($admin)->get(route('admin.reports.index', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]));
        $filtered->assertStatus(200);
        $filtered->assertSee('Memfilter laporan penjualan');
        $filtered->assertSee('Melihat laporan penjualan');
    }

    public function test_ekspor_laporan_tercatat_di_log_aktivitas(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.reports.export', ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']))
            ->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Mengekspor laporan penjualan (Excel)');
    }
}
