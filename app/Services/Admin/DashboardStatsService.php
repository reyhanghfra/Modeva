<?php

namespace App\Services\Admin;

use App\Support\Rupiah;

class DashboardStatsService
{
    /**
     * Peta status pesanan & inventori ke label dan varian badge.
     * Dikelola di satu tempat sesuai spesifikasi.
     *
     * @var array<string, array{label: string, variant: string}>
     */
    protected const STATUS_MAP = [
        'verifikasi_pembayaran' => [
            'label' => 'Terverifikasi',
            'variant' => 'success',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'variant' => 'success',
        ],
        'menunggu_pembayaran' => [
            'label' => 'Menunggu Pembayaran',
            'variant' => 'warning',
        ],
        'diproses' => [
            'label' => 'Diproses',
            'variant' => 'warning',
        ],
        'dikirim' => [
            'label' => 'Dikirim',
            'variant' => 'warning',
        ],
        'sisa_sedikit' => [
            'label' => 'Sisa Sedikit',
            'variant' => 'warning',
        ],
        'habis' => [
            'label' => 'Habis',
            'variant' => 'danger',
        ],
    ];

    /**
     * Mendapatkan metadata status berdasarkan kode status.
     *
     * @return array{label: string, variant: string}
     */
    public static function getStatusMeta(string $code): array
    {
        return self::STATUS_MAP[$code] ?? [
            'label' => ucfirst(str_replace('_', ' ', $code)),
            'variant' => 'warning',
        ];
    }

    /**
     * Mendapatkan ringkasan 4 metrik utama dasbor.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        // TODO: ganti dengan query asli setelah tabel orders/products/stocks tersedia
        return [
            'total_pendapatan' => [
                'label' => 'Total Pendapatan',
                'value' => 6982000,
                'formatted' => Rupiah::format(6982000),
                'subtext' => '+12% dari bulan lalu',
                'icon' => 'receipt',
            ],
            'total_pesanan' => [
                'label' => 'Total Pesanan',
                'value' => 10,
                'formatted' => '10',
                'subtext' => null,
                'icon' => 'box',
            ],
            'total_produk' => [
                'label' => 'Total Produk',
                'value' => 12,
                'formatted' => '12',
                'subtext' => null,
                'icon' => 'layers',
            ],
            'laba_bersih' => [
                'label' => 'Laba Bersih',
                'value' => 3462000,
                'formatted' => Rupiah::format(3462000),
                'subtext' => '+9% dari bulan lalu',
                'icon' => 'bar-chart',
            ],
        ];
    }

    /**
     * Mendapatkan data performa penjualan 7 hari terakhir (Senin - Minggu).
     *
     * @return array<int, array{day: string, day_full: string, amount: int, formatted_amount: string, percentage: int, is_today: bool}>
     */
    public function salesLast7Days(): array
    {
        // TODO: ganti dengan query asli setelah tabel orders/products/stocks tersedia
        $raw = [
            ['day' => 'Sen', 'day_full' => 'Senin', 'amount' => 650000, 'is_today' => false],
            ['day' => 'Sel', 'day_full' => 'Selasa', 'amount' => 920000, 'is_today' => false],
            ['day' => 'Rab', 'day_full' => 'Rabu', 'amount' => 1100000, 'is_today' => false],
            ['day' => 'Kam', 'day_full' => 'Kamis', 'amount' => 780000, 'is_today' => false],
            ['day' => 'Jum', 'day_full' => 'Jumat', 'amount' => 1250000, 'is_today' => false],
            ['day' => 'Sab', 'day_full' => 'Sabtu', 'amount' => 1420000, 'is_today' => false],
            ['day' => 'Min', 'day_full' => 'Minggu', 'amount' => 862000, 'is_today' => true],
        ];

        $max = max(array_column($raw, 'amount'));

        return array_map(function ($item) use ($max) {
            $percentage = $max > 0 ? (int) round(($item['amount'] / $max) * 100) : 0;
            // Pastikan bar tetap memiliki tinggi minimal agar terlihat cantik jika nilai kecil
            $clampedPercentage = max(12, $percentage);

            return [
                'day' => $item['day'],
                'day_full' => $item['day_full'],
                'amount' => $item['amount'],
                'formatted_amount' => Rupiah::format($item['amount']),
                'percentage' => $clampedPercentage,
                'is_today' => $item['is_today'],
            ];
        }, $raw);
    }

    /**
     * Mendapatkan daftar produk dengan stok menipis atau habis.
     *
     * @return array<int, array<string, mixed>>
     */
    public function lowStock(): array
    {
        // TODO: ganti dengan query asli setelah tabel orders/products/stocks tersedia
        $items = [
            [
                'name' => 'Blazer Wol Oversize Malam',
                'variant' => 'Hitam · M',
                'stock' => 2,
                'status_code' => 'sisa_sedikit',
                'badge_label' => '2 sisa',
            ],
            [
                'name' => 'Blazer Wol Oversize Malam',
                'variant' => 'Hitam · L',
                'stock' => 0,
                'status_code' => 'habis',
                'badge_label' => 'Habis',
            ],
            [
                'name' => 'Blazer Wol Oversize Malam',
                'variant' => 'Hitam · XL',
                'stock' => 3,
                'status_code' => 'sisa_sedikit',
                'badge_label' => '3 sisa',
            ],
            [
                'name' => 'Blazer Wol Oversize Malam',
                'variant' => 'Arang · M',
                'stock' => 1,
                'status_code' => 'sisa_sedikit',
                'badge_label' => '1 sisa',
            ],
            [
                'name' => 'Setelan Linen Pasir',
                'variant' => 'Krem · M',
                'stock' => 1,
                'status_code' => 'sisa_sedikit',
                'badge_label' => '1 sisa',
            ],
            [
                'name' => 'Dress Midi Linen Kopi',
                'variant' => 'Hitam · S',
                'stock' => 2,
                'status_code' => 'sisa_sedikit',
                'badge_label' => '2 sisa',
            ],
        ];

        return array_map(function ($item) {
            $meta = self::getStatusMeta($item['status_code']);
            return [
                'name' => $item['name'],
                'variant' => $item['variant'],
                'stock' => $item['stock'],
                'status_code' => $item['status_code'],
                'status_label' => $item['badge_label'] ?? $meta['label'],
                'status_variant' => $meta['variant'],
            ];
        }, $items);
    }

    /**
     * Mendapatkan daftar transaksi terbaru.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recentTransactions(): array
    {
        // TODO: ganti dengan query asli setelah tabel orders/products/stocks tersedia
        $items = [
            [
                'customer_name' => 'Anindya Putri',
                'invoice' => 'MDV-250611',
                'date' => '23 Nov 2026',
                'amount' => 1450000,
                'status_code' => 'selesai',
                'status_label' => 'Selesai',
            ],
            [
                'customer_name' => 'Maya Lestari',
                'invoice' => 'MDV-250610',
                'date' => '23 Nov 2026',
                'amount' => 850000,
                'status_code' => 'dikirim',
                'status_label' => 'Dikirim',
            ],
            [
                'customer_name' => 'Rizky Pratama',
                'invoice' => 'MDV-250609',
                'date' => '22 Nov 2026',
                'amount' => 420000,
                'status_code' => 'diproses',
                'status_label' => 'Diproses',
            ],
            [
                'customer_name' => 'Siti Nurhaliza',
                'invoice' => 'MDV-250608',
                'date' => '22 Nov 2026',
                'amount' => 1890000,
                'status_code' => 'verifikasi_pembayaran',
                'status_label' => 'Verifikasi',
            ],
            [
                'customer_name' => 'Bagas Wicaksono',
                'invoice' => 'MDV-250607',
                'date' => '21 Nov 2026',
                'amount' => 650000,
                'status_code' => 'menunggu_pembayaran',
                'status_label' => 'Menunggu',
            ],
        ];

        return array_map(function ($item) {
            $meta = self::getStatusMeta($item['status_code']);
            return [
                'customer_name' => $item['customer_name'],
                'invoice_code' => $item['invoice'] . ' · ' . $item['date'],
                'amount' => $item['amount'],
                'formatted_amount' => Rupiah::format($item['amount']),
                'status_code' => $item['status_code'],
                'status_label' => $item['status_label'] ?? $meta['label'],
                'status_variant' => $meta['variant'],
            ];
        }, $items);
    }

    /**
     * Mendapatkan daftar produk terlaris.
     *
     * @return array<int, array<string, mixed>>
     */
    public function topProducts(): array
    {
        // TODO: ganti dengan query asli setelah tabel orders/products/stocks tersedia
        return [
            [
                'rank' => 1,
                'name' => 'Sneakers Kulit Putih Pagi',
                'sales_count' => 212,
                'formatted_sales' => '212 terjual',
                'thumbnail' => null,
            ],
            [
                'rank' => 2,
                'name' => 'Tas Tangan Kulit Camel Premium',
                'sales_count' => 187,
                'formatted_sales' => '187 terjual',
                'thumbnail' => null,
            ],
            [
                'rank' => 3,
                'name' => 'Tote Kanvas Pantai',
                'sales_count' => 164,
                'formatted_sales' => '164 terjual',
                'thumbnail' => null,
            ],
            [
                'rank' => 4,
                'name' => 'Dress Kemeja Linen Senja',
                'sales_count' => 148,
                'formatted_sales' => '148 terjual',
                'thumbnail' => null,
            ],
            [
                'rank' => 5,
                'name' => 'Sandal Hak Blok Aksara',
                'sales_count' => 133,
                'formatted_sales' => '133 terjual',
                'thumbnail' => null,
            ],
        ];
    }
}
