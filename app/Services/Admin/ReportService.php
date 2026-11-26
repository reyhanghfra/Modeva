<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\User;
use App\Support\Rupiah;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ReportService
{
    /**
     * Persentase estimasi margin laba kotor yang dipakai saat kolom HPP belum tersedia.
     * Ganti konstanta ini satu kali ketika HPP sudah ditambahkan ke skema.
     */
    public const ESTIMATED_MARGIN_PERCENT = 40;

    /**
     * Ringkasan metrik utama untuk periode yang dipilih.
     *
     * @return array{
     *     total_penjualan: int,
     *     total_penjualan_formatted: string,
     *     jumlah_transaksi: int,
     *     rata_rata_pesanan: int,
     *     rata_rata_pesanan_formatted: string,
     *     estimasi_laba: int,
     *     estimasi_laba_formatted: string,
     *     margin_persen: int,
     *     total_unit_terjual: int,
     * }
     */
    public function summary(Carbon $start, Carbon $end): array
    {
        $row = Order::query()
            ->where('status', Order::STATUS_SELESAI)
            ->whereBetween('created_at', [$start->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(total), 0) as total_penjualan')
            ->first();

        $totalPenjualan = (int) ($row?->total_penjualan ?? 0);
        $jumlahTransaksi = (int) ($row?->jumlah ?? 0);
        $rataRata = $jumlahTransaksi > 0 ? (int) round($totalPenjualan / $jumlahTransaksi) : 0;
        $estimasiLaba = (int) round($totalPenjualan * self::ESTIMATED_MARGIN_PERCENT / 100);

        $totalUnit = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', Order::STATUS_SELESAI)
            ->whereBetween('orders.created_at', [$start->startOfDay(), $end->copy()->endOfDay()])
            ->sum('order_items.quantity');

        return [
            'total_penjualan' => $totalPenjualan,
            'total_penjualan_formatted' => Rupiah::format($totalPenjualan),
            'jumlah_transaksi' => $jumlahTransaksi,
            'rata_rata_pesanan' => $rataRata,
            'rata_rata_pesanan_formatted' => Rupiah::format($rataRata),
            'estimasi_laba' => $estimasiLaba,
            'estimasi_laba_formatted' => Rupiah::format($estimasiLaba),
            'margin_persen' => self::ESTIMATED_MARGIN_PERCENT,
            'total_unit_terjual' => (int) $totalUnit,
        ];
    }

    /**
     * Daftar produk terlaris berdasarkan jumlah unit terjual dalam periode.
     *
     * @return Collection<int, array{
     *     rank: int,
     *     product_name: string,
     *     qty_terjual: int,
     *     pendapatan: int,
     *     pendapatan_formatted: string,
     * }>
     */
    public function topProducts(Carbon $start, Carbon $end, int $limit = 10): Collection
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', Order::STATUS_SELESAI)
            ->whereBetween('orders.created_at', [$start->startOfDay(), $end->copy()->endOfDay()])
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty_terjual')
            ->limit($limit)
            ->select([
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as qty_terjual'),
                DB::raw('SUM(order_items.subtotal) as pendapatan'),
            ])
            ->get();

        return $rows->values()->map(function ($row, int $index): array {
            return [
                'rank' => $index + 1,
                'product_name' => $row->product_name,
                'qty_terjual' => (int) $row->qty_terjual,
                'pendapatan' => (int) $row->pendapatan,
                'pendapatan_formatted' => Rupiah::format((int) $row->pendapatan),
            ];
        });
    }

    /**
     * Data grafik penjualan per bulan untuk 12 bulan terakhir.
     * Dipakai saat tidak ada filter periode aktif (tampilan default).
     *
     * @return array<int, array{
     *     label: string,
     *     label_full: string,
     *     amount: int,
     *     formatted_amount: string,
     *     percentage: int,
     *     is_current: bool,
     * }>
     */
    public function salesByMonth(int $months = 12): array
    {
        $rows = Order::query()
            ->where('status', Order::STATUS_SELESAI)
            ->where('created_at', '>=', now()->subMonths($months - 1)->startOfMonth())
            ->groupBy('period')
            ->orderBy('period')
            ->selectRaw($this->dateFormatExpr('created_at', 'month').' as period, COALESCE(SUM(total), 0) as total')
            ->pluck('total', 'period');

        // Isi bulan yang tidak memiliki transaksi dengan nilai 0
        $result = [];
        $currentMonth = now()->format('Y-m');

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i)->startOfMonth();
            $period = $date->format('Y-m');
            $result[$period] = (int) ($rows[$period] ?? 0);
        }

        $max = max($result) ?: 1;

        return array_values(array_map(function (int $amount, string $period) use ($max, $currentMonth): array {
            $date = Carbon::createFromFormat('Y-m', $period);

            return [
                'label' => $date->translatedFormat('M'),
                'label_full' => $date->translatedFormat('F Y'),
                'amount' => $amount,
                'formatted_amount' => Rupiah::format($amount),
                'percentage' => max(5, (int) round($amount / $max * 100)),
                'is_current' => $period === $currentMonth,
            ];
        }, $result, array_keys($result)));
    }

    /**
     * Data grafik penjualan harian dalam rentang periode terpilih.
     * Dipakai saat filter aktif dan rentang ≤ 62 hari.
     *
     * @return array<int, array{
     *     label: string,
     *     label_full: string,
     *     amount: int,
     *     formatted_amount: string,
     *     percentage: int,
     *     is_current: bool,
     * }>
     */
    public function salesByDay(Carbon $start, Carbon $end): array
    {
        $rows = Order::query()
            ->where('status', Order::STATUS_SELESAI)
            ->whereBetween('created_at', [$start->startOfDay(), $end->copy()->endOfDay()])
            ->groupBy('period')
            ->orderBy('period')
            ->selectRaw($this->dateFormatExpr('created_at', 'day').' as period, COALESCE(SUM(total), 0) as total')
            ->pluck('total', 'period');

        $result = [];
        $today = today()->format('Y-m-d');
        $cursor = $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $period = $cursor->format('Y-m-d');
            $result[$period] = (int) ($rows[$period] ?? 0);
            $cursor->addDay();
        }

        $max = max($result) ?: 1;

        return array_values(array_map(function (int $amount, string $period) use ($max, $today): array {
            $date = Carbon::createFromFormat('Y-m-d', $period);

            return [
                'label' => $date->format('d'),
                'label_full' => $date->translatedFormat('d F Y'),
                'amount' => $amount,
                'formatted_amount' => Rupiah::format($amount),
                'percentage' => max(5, (int) round($amount / $max * 100)),
                'is_current' => $period === $today,
            ];
        }, $result, array_keys($result)));
    }

    /**
     * Menentukan data grafik yang sesuai berdasarkan rentang periode.
     * - Periode ≤ 62 hari  → bar harian
     * - Periode > 62 hari  → bar bulanan (12 bulan terakhir)
     *
     * @return array<int, array<string, mixed>>
     */
    public function chartData(Carbon $start, Carbon $end, bool $isFiltered): array
    {
        if ($isFiltered && $start->diffInDays($end) <= 62) {
            return $this->salesByDay($start, $end);
        }

        return $this->salesByMonth();
    }

    /**
     * Daftar pesanan selesai dalam rentang periode untuk kebutuhan rincian ekspor.
     *
     * @return Collection<int, Order>
     */
    public function completedOrders(Carbon $start, Carbon $end): Collection
    {
        return Order::query()
            ->with('user')
            ->where('status', Order::STATUS_SELESAI)
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->latest('created_at')
            ->get();
    }

    /**
     * Mencatat aktivitas admin pada modul laporan penjualan ke file log khusus.
     *
     * @param  array<string, mixed>  $extra
     */
    public function recordActivity(
        string $action,
        ?User $user,
        Carbon $start,
        Carbon $end,
        bool $isFiltered,
        ?string $ip = null,
        array $extra = []
    ): void {
        try {
            $context = array_merge([
                'timestamp' => now()->toIso8601String(),
                'action' => $action,
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'Sistem',
                'user_email' => $user?->email ?? '-',
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'is_filtered' => $isFiltered,
                'ip' => $ip,
            ], $extra);

            Log::info("Laporan Penjualan: {$action}", $context);

            $path = $this->activityLogPath();
            File::ensureDirectoryExists(dirname($path));
            File::append($path, json_encode($context, JSON_UNESCAPED_UNICODE).PHP_EOL);
        } catch (\Throwable) {
            // Jangan gagalkan tampilan laporan jika penulisan log bermasalah
        }
    }

    /**
     * Mengambil daftar aktivitas terbaru pada modul laporan penjualan.
     *
     * @return Collection<int, array{
     *     timestamp: string,
     *     action: string,
     *     user_name: string,
     *     user_email: string,
     *     period: string,
     *     badge_label: string,
     *     badge_variant: string,
     * }>
     */
    public function recentActivities(int $limit = 8): Collection
    {
        $path = $this->activityLogPath();

        if (! File::exists($path)) {
            return collect();
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || $lines === []) {
            return collect();
        }

        $entries = [];

        foreach (array_reverse($lines) as $line) {
            $context = json_decode(trim($line), true);
            if (! is_array($context) || empty($context['action'])) {
                continue;
            }

            $action = (string) $context['action'];
            $badge = $this->activityBadge($action);

            try {
                $timestamp = Carbon::parse((string) ($context['timestamp'] ?? now()))->translatedFormat('d M Y, H:i');
            } catch (\Throwable) {
                $timestamp = (string) ($context['timestamp'] ?? '-');
            }

            $period = '-';
            if (! empty($context['start_date']) && ! empty($context['end_date'])) {
                try {
                    $startLabel = Carbon::parse((string) $context['start_date'])->translatedFormat('d M Y');
                    $endLabel = Carbon::parse((string) $context['end_date'])->translatedFormat('d M Y');
                    $period = "{$startLabel} – {$endLabel}";
                } catch (\Throwable) {
                    $period = "{$context['start_date']} – {$context['end_date']}";
                }
            }

            $entries[] = [
                'timestamp' => $timestamp,
                'action' => $action,
                'user_name' => (string) ($context['user_name'] ?? 'Admin'),
                'user_email' => (string) ($context['user_email'] ?? '-'),
                'period' => $period,
                'badge_label' => $badge['label'],
                'badge_variant' => $badge['variant'],
            ];

            if (count($entries) >= $limit) {
                break;
            }
        }

        return collect($entries);
    }

    /**
     * Lokasi file log aktivitas khusus laporan penjualan.
     */
    public function activityLogPath(): string
    {
        return storage_path('logs/report-activity.log');
    }

    /**
     * Menentukan label dan warna badge berdasarkan jenis aktivitas laporan.
     *
     * @return array{label: string, variant: string}
     */
    private function activityBadge(string $action): array
    {
        $lower = strtolower($action);

        if (str_contains($lower, 'ekspor')) {
            return ['label' => 'Ekspor', 'variant' => 'success'];
        }

        if (str_contains($lower, 'filter')) {
            return ['label' => 'Filter', 'variant' => 'warning'];
        }

        return ['label' => 'Lihat', 'variant' => 'secondary'];
    }

    /**
     * Ekspresi SQL format tanggal yang kompatibel dengan MySQL/MariaDB dan SQLite.
     */
    private function dateFormatExpr(string $column, string $unit): string
    {
        $format = $unit === 'month' ? '%Y-%m' : '%Y-%m-%d';

        if (DB::getDriverName() === 'sqlite') {
            return "strftime('{$format}', {$column})";
        }

        return "DATE_FORMAT({$column}, '{$format}')";
    }
}
