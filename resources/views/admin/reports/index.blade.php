@extends('layouts.admin')

@section('title', 'Laporan Penjualan - Dasbor Modeva')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <x-admin.page-header
        title="Laporan Penjualan"
        subtitle="Rekap penjualan berdasarkan pesanan berstatus Selesai"
    >
        <x-slot:action>
            @if ($isFiltered)
                <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-full">
                    <x-icon name="alert-triangle" class="w-3.5 h-3.5" strokeWidth="2" />
                    Filter aktif
                </span>
            @endif

            <x-admin.button
                :href="route('admin.reports.export', request()->only(['start_date', 'end_date']))"
                variant="outline"
                size="sm"
                icon="download"
            >
                Ekspor Excel
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    {{-- ─── Filter Periode ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] p-5 sm:p-6">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex flex-col sm:flex-row gap-4 flex-1">
                <div class="space-y-1.5">
                    <label for="start_date" class="block text-xs font-medium text-[#1C1917]">
                        Tanggal Mulai
                    </label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        value="{{ request('start_date', $isFiltered ? $start->format('Y-m-d') : '') }}"
                        class="px-4 py-2.5 text-sm bg-white text-[#1C1917] rounded-xl border border-[#EFECE6] focus:border-[#222] focus:ring-1 focus:ring-[#222] focus:outline-none shadow-2xs transition-colors"
                    >
                </div>

                <div class="space-y-1.5">
                    <label for="end_date" class="block text-xs font-medium text-[#1C1917]">
                        Tanggal Akhir
                    </label>
                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        value="{{ request('end_date', $isFiltered ? $end->format('Y-m-d') : '') }}"
                        class="px-4 py-2.5 text-sm bg-white text-[#1C1917] rounded-xl border border-[#EFECE6] focus:border-[#222] focus:ring-1 focus:ring-[#222] focus:outline-none shadow-2xs transition-colors"
                    >
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <x-admin.button type="submit" variant="primary" size="sm" icon="bar-chart">
                    Terapkan Filter
                </x-admin.button>

                @if ($isFiltered)
                    <x-admin.button
                        :href="route('admin.reports.index')"
                        variant="secondary"
                        size="sm"
                    >
                        Reset
                    </x-admin.button>
                @endif
            </div>
        </form>

        @if ($isFiltered)
            <p class="mt-3 text-xs text-[#78716C]">
                Menampilkan data
                <span class="font-medium text-[#1C1917]">{{ $start->translatedFormat('d F Y') }}</span>
                —
                <span class="font-medium text-[#1C1917]">{{ $end->translatedFormat('d F Y') }}</span>
            </p>
        @else
            <p class="mt-3 text-xs text-[#78716C]">
                Menampilkan data bulan berjalan:
                <span class="font-medium text-[#1C1917]">{{ $start->translatedFormat('F Y') }}</span>
            </p>
        @endif
    </div>

    {{-- ─── 4 Stat Cards ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- Total Penjualan --}}
        <x-admin.stat-card
            label="Total Penjualan"
            :value="$summary['total_penjualan_formatted']"
            icon="receipt"
            :subtext="'dari ' . $summary['jumlah_transaksi'] . ' transaksi selesai'"
        />

        {{-- Jumlah Transaksi --}}
        <x-admin.stat-card
            label="Transaksi Selesai"
            :value="(string) $summary['jumlah_transaksi']"
            icon="check-circle"
            :subtext="$summary['total_unit_terjual'] . ' unit produk terjual'"
        />

        {{-- Rata-rata per Pesanan --}}
        <x-admin.stat-card
            label="Rata-rata per Pesanan"
            :value="$summary['jumlah_transaksi'] > 0 ? $summary['rata_rata_pesanan_formatted'] : '—'"
            icon="bar-chart"
            :subtext="$summary['jumlah_transaksi'] > 0 ? 'nilai rata-rata per transaksi' : 'belum ada transaksi selesai'"
        />

        {{-- Estimasi Laba --}}
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col justify-between transition-shadow hover:shadow-[0_4px_20px_rgba(0,0,0,0.04)]">
            <div class="flex items-start justify-between gap-3">
                <span class="text-xs sm:text-sm font-medium text-[#78716C] tracking-wide">
                    Estimasi Laba
                </span>
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-amber-50 flex items-center justify-center shrink-0">
                    <x-icon name="layers" class="w-5 h-5 text-amber-600" strokeWidth="1.75" />
                </div>
            </div>
            <div class="mt-4 sm:mt-5">
                <div class="font-serif text-3xl sm:text-4xl text-[#1C1917] font-semibold tracking-tight truncate leading-tight">
                    {{ $summary['estimasi_laba_formatted'] }}
                </div>
                <p class="text-xs sm:text-[13px] mt-2 font-medium text-amber-700 flex items-center gap-1">
                    <x-icon name="alert-triangle" class="w-3.5 h-3.5 shrink-0" strokeWidth="2" />
                    Estimasi {{ $summary['margin_persen'] }}% margin (HPP belum ada)
                </p>
            </div>
        </div>

    </div>

    {{-- ─── Grafik Penjualan ────────────────────────────────────────── --}}
    <x-admin.panel
        :title="$chartIsDaily ? 'Grafik Penjualan Harian' : 'Grafik Penjualan Bulanan'"
        icon="bar-chart"
    >
        <x-slot:headerRight>
            <span class="text-xs font-medium text-[#78716C] bg-[#EDE7DF] px-3 py-1 rounded-full select-none">
                {{ $chartIsDaily ? ($start->format('d M') . ' – ' . $end->format('d M Y')) : '12 bulan terakhir' }}
            </span>
        </x-slot:headerRight>

        @if (count($chartData) > 0 && collect($chartData)->sum('amount') > 0)
            <div class="pt-4 flex-1 flex flex-col justify-end overflow-x-auto">
                <div
                    class="h-56 sm:h-64 flex items-end justify-between gap-1 sm:gap-2 px-2 pb-2 border-b border-[#EFECE6] min-w-0"
                    style="min-width: {{ count($chartData) * 36 }}px"
                    role="region"
                    aria-label="{{ $chartIsDaily ? 'Grafik Penjualan Harian' : 'Grafik Penjualan Bulanan' }}"
                >
                    @foreach ($chartData as $bar)
                        <div class="flex-1 flex flex-col items-center h-full justify-end group min-w-0">
                            <div class="w-full flex justify-center h-full items-end">
                                <div
                                    class="w-full max-w-[36px] sm:max-w-[44px] rounded-t-lg transition-all duration-300 {{ $bar['is_current'] ? 'bg-[#222222]' : 'bg-[#D0C5B8] group-hover:bg-[#B8A99A]' }}"
                                    style="height: {{ $bar['percentage'] }}%;"
                                    title="{{ $bar['label_full'] }}: {{ $bar['formatted_amount'] }}"
                                    aria-label="{{ $bar['label_full'] }}: {{ $bar['formatted_amount'] }}"
                                    tabindex="0"
                                ></div>
                            </div>
                            <span class="text-[10px] sm:text-xs font-medium text-[#78716C] mt-2 group-hover:text-[#1C1917] transition-colors truncate max-w-full text-center">
                                {{ $bar['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <x-icon name="bar-chart" class="w-10 h-10 text-[#D0C5B8] mx-auto mb-3" strokeWidth="1.5" />
                <p class="text-sm font-medium text-[#1C1917]">Belum ada data penjualan</p>
                <p class="text-xs text-[#78716C] mt-1">Tidak ada transaksi selesai dalam periode ini.</p>
            </div>
        @endif
    </x-admin.panel>

    {{-- ─── Produk Terlaris ────────────────────────────────────────── --}}
    <x-admin.panel title="Produk Terlaris" icon="layers">
        <x-slot:headerRight>
            <span class="text-xs font-medium text-[#78716C] bg-[#EDE7DF] px-3 py-1 rounded-full select-none">
                Top {{ $topProducts->count() }}
            </span>
        </x-slot:headerRight>

        @if ($topProducts->isNotEmpty())
            <div class="overflow-x-auto -mx-6 sm:-mx-7">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#EFECE6]">
                            <th class="px-6 sm:px-7 py-3 text-left text-xs font-semibold text-[#78716C] uppercase tracking-wider w-10">#</th>
                            <th class="px-6 sm:px-7 py-3 text-left text-xs font-semibold text-[#78716C] uppercase tracking-wider">Produk</th>
                            <th class="px-6 sm:px-7 py-3 text-right text-xs font-semibold text-[#78716C] uppercase tracking-wider">Qty Terjual</th>
                            <th class="px-6 sm:px-7 py-3 text-right text-xs font-semibold text-[#78716C] uppercase tracking-wider">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EFEA]">
                        @foreach ($topProducts as $product)
                            <tr class="hover:bg-[#FAF8F5]/60 transition-colors">
                                <td class="px-6 sm:px-7 py-3.5 whitespace-nowrap">
                                    <span class="font-serif text-base font-semibold text-[#A8A29E]">
                                        {{ $product['rank'] }}
                                    </span>
                                </td>
                                <td class="px-6 sm:px-7 py-3.5">
                                    <span class="font-medium text-[#1C1917]">
                                        {{ $product['product_name'] }}
                                    </span>
                                </td>
                                <td class="px-6 sm:px-7 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-semibold text-[#1C1917]">{{ number_format($product['qty_terjual']) }}</span>
                                    <span class="text-xs text-[#78716C] ml-1">unit</span>
                                </td>
                                <td class="px-6 sm:px-7 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-semibold text-[#1C1917]">{{ $product['pendapatan_formatted'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <x-icon name="layers" class="w-10 h-10 text-[#D0C5B8] mx-auto mb-3" strokeWidth="1.5" />
                <p class="text-sm font-medium text-[#1C1917]">Belum ada data produk</p>
                <p class="text-xs text-[#78716C] mt-1">Tidak ada item terjual dalam periode ini.</p>
            </div>
        @endif
    </x-admin.panel>

    {{-- ─── Log Aktivitas Laporan ──────────────────────────────────── --}}
    <x-admin.panel title="Log Aktivitas Laporan" icon="shield-check">
        <x-slot:headerRight>
            <span class="text-xs font-medium text-[#78716C] bg-[#EDE7DF] px-3 py-1 rounded-full select-none">
                {{ $recentActivities->count() }} aktivitas terbaru
            </span>
        </x-slot:headerRight>

        @if ($recentActivities->isNotEmpty())
            <div class="divide-y divide-[#F3EFEA]">
                @foreach ($recentActivities as $activity)
                    <div class="py-3 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                        <div class="min-w-0 space-y-0.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <x-admin.status-badge
                                    :variant="$activity['badge_variant']"
                                    :label="$activity['badge_label']"
                                />
                                <span class="text-sm font-medium text-[#1C1917]">
                                    {{ $activity['action'] }}
                                </span>
                            </div>
                            <p class="text-xs text-[#78716C]">
                                Oleh <span class="font-medium text-[#1C1917]">{{ $activity['user_name'] }}</span>
                                ({{ $activity['user_email'] }}) · Periode: {{ $activity['period'] }}
                            </p>
                        </div>

                        <span class="text-xs text-[#78716C] whitespace-nowrap shrink-0">
                            {{ $activity['timestamp'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <x-icon name="shield-check" class="w-8 h-8 text-[#D0C5B8] mx-auto mb-2" strokeWidth="1.5" />
                <p class="text-sm font-medium text-[#1C1917]">Belum ada log aktivitas</p>
                <p class="text-xs text-[#78716C] mt-1">Aktivitas melihat, memfilter, atau mengekspor laporan akan tercatat di sini.</p>
            </div>
        @endif
    </x-admin.panel>

    {{-- ─── Catatan Transparansi Estimasi Laba ─────────────────────── --}}
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 sm:p-6 flex gap-4">
        <x-icon name="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" strokeWidth="1.75" />
        <div class="space-y-1.5 text-sm">
            <p class="font-semibold text-amber-900">Catatan: Estimasi Laba</p>
            <p class="text-amber-800">
                Kolom <code class="font-mono bg-amber-100 px-1 rounded text-xs">HPP (Harga Pokok Penjualan)</code> belum tersedia di database.
                Angka <strong>Estimasi Laba</strong> dihitung menggunakan asumsi margin
                <strong>{{ \App\Services\Admin\ReportService::ESTIMATED_MARGIN_PERCENT }}%</strong> dari total penjualan
                (<code class="font-mono bg-amber-100 px-1 rounded text-xs">total_penjualan × {{ \App\Services\Admin\ReportService::ESTIMATED_MARGIN_PERCENT / 100 }}</code>).
            </p>
            <p class="text-amber-700 text-xs">
                Untuk menghitung laba aktual, tambahkan kolom <code class="font-mono bg-amber-100 px-1 rounded text-xs">cost_price</code> ke tabel
                <code class="font-mono bg-amber-100 px-1 rounded text-xs">order_items</code>, lalu ubah konstanta
                <code class="font-mono bg-amber-100 px-1 rounded text-xs">ReportService::ESTIMATED_MARGIN_PERCENT</code> menjadi kalkulasi nyata.
            </p>
        </div>
    </div>

</div>
@endsection
