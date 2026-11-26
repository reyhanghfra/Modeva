<!DOCTYPE html>
<html lang="id" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Modeva</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #1C1917; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 18px; }
        th, td { border: 1px solid #D6D3D1; padding: 6px 10px; text-align: left; }
        th { background-color: #EDE7DF; font-weight: bold; }
        .title { font-size: 14pt; font-weight: bold; border: none; padding-left: 0; }
        .subtitle { font-size: 10pt; color: #57534E; border: none; padding-left: 0; }
        .section-title { font-size: 12pt; font-weight: bold; background-color: #222222; color: #FFFFFF; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="4" class="title">LAPORAN PENJUALAN MODEVA</td>
        </tr>
        <tr>
            <td colspan="4" class="subtitle">
                Periode: {{ $start->translatedFormat('d F Y') }} s/d {{ $end->translatedFormat('d F Y') }}
                ({{ $isFiltered ? 'Filter Kustom' : 'Bulan Berjalan' }})
            </td>
        </tr>
        <tr>
            <td colspan="4" class="subtitle">Status Pesanan: Selesai</td>
        </tr>
    </table>

    <table>
        <tr>
            <th colspan="2" class="section-title">RINGKASAN PENJUALAN</th>
        </tr>
        <tr>
            <th>Total Penjualan</th>
            <td class="num">{{ $summary['total_penjualan'] }}</td>
        </tr>
        <tr>
            <th>Transaksi Selesai</th>
            <td class="num">{{ $summary['jumlah_transaksi'] }}</td>
        </tr>
        <tr>
            <th>Total Unit Terjual</th>
            <td class="num">{{ $summary['total_unit_terjual'] }}</td>
        </tr>
        <tr>
            <th>Rata-rata per Pesanan</th>
            <td class="num">{{ $summary['rata_rata_pesanan'] }}</td>
        </tr>
        <tr>
            <th>Estimasi Laba (Margin {{ $summary['margin_persen'] }}% - HPP belum tersedia)</th>
            <td class="num">{{ $summary['estimasi_laba'] }}</td>
        </tr>
    </table>

    <table>
        <tr>
            <th colspan="4" class="section-title">PRODUK TERLARIS</th>
        </tr>
        <tr>
            <th>Peringkat</th>
            <th>Nama Produk</th>
            <th class="num">Qty Terjual</th>
            <th class="num">Pendapatan (Rp)</th>
        </tr>
        @forelse ($topProducts as $product)
            <tr>
                <td>{{ $product['rank'] }}</td>
                <td>{{ $product['product_name'] }}</td>
                <td class="num">{{ $product['qty_terjual'] }}</td>
                <td class="num">{{ $product['pendapatan'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">Tidak ada produk terjual pada periode ini.</td>
            </tr>
        @endforelse
    </table>

    <table>
        <tr>
            <th colspan="4" class="section-title">RINCIAN TRANSAKSI SELESAI</th>
        </tr>
        <tr>
            <th>Nomor Pesanan</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th class="num">Total (Rp)</th>
        </tr>
        @forelse ($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $order->user?->name ?? '-' }}</td>
                <td class="num">{{ $order->total }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">Tidak ada transaksi selesai pada periode ini.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>
