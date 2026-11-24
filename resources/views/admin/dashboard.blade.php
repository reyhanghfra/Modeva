@extends('layouts.admin')

@section('title', 'Ringkasan - Dasbor Modeva')

@section('content')
<div class="space-y-8">
    {{-- Page Header --}}
    <div>
        <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-[#1C1917] tracking-tight">
            Ringkasan
        </h1>
        <p class="text-sm text-[#78716C] mt-1 font-normal">
            Performa toko MODEVA secara keseluruhan
        </p>
    </div>

    {{-- Row 1: 4 Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-admin.stat-card
            :label="$summary['total_pendapatan']['label']"
            :value="$summary['total_pendapatan']['formatted']"
            :icon="$summary['total_pendapatan']['icon']"
            :subtext="$summary['total_pendapatan']['subtext']"
        />

        <x-admin.stat-card
            :label="$summary['total_pesanan']['label']"
            :value="$summary['total_pesanan']['formatted']"
            :icon="$summary['total_pesanan']['icon']"
            :subtext="$summary['total_pesanan']['subtext']"
        />

        <x-admin.stat-card
            :label="$summary['total_produk']['label']"
            :value="$summary['total_produk']['formatted']"
            :icon="$summary['total_produk']['icon']"
            :subtext="$summary['total_produk']['subtext']"
        />

        <x-admin.stat-card
            :label="$summary['laba_bersih']['label']"
            :value="$summary['laba_bersih']['formatted']"
            :icon="$summary['laba_bersih']['icon']"
            :subtext="$summary['laba_bersih']['subtext']"
        />
    </div>

    {{-- Row 2: Sales Chart (2/3) + Low Stock (1/3) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Panel Performa Penjualan --}}
        <div class="lg:col-span-2">
            <x-admin.panel title="Performa Penjualan">
                <x-slot:headerRight>
                    <span class="text-xs font-medium text-[#78716C] bg-[#EDE7DF] px-3 py-1 rounded-full select-none">
                        7 hari terakhir
                    </span>
                </x-slot:headerRight>

                <div class="pt-4 flex-1 flex flex-col justify-end">
                    {{-- Pure HTML/CSS Bar Chart --}}
                    <div class="h-56 sm:h-64 flex items-end justify-between gap-2 sm:gap-6 px-2 sm:px-4 pb-2 border-b border-[#EFECE6]" role="region" aria-label="Grafik Penjualan 7 Hari Terakhir">
                        @foreach ($salesChart as $bar)
                            <div class="flex-1 flex flex-col items-center h-full justify-end group">
                                {{-- Bar Container --}}
                                <div class="w-full flex justify-center h-full items-end">
                                    <div
                                        class="w-full max-w-[38px] sm:max-w-[48px] rounded-t-lg transition-all duration-300 {{ $bar['is_today'] ? 'bg-[#222222]' : 'bg-[#D0C5B8] group-hover:bg-[#C2B5A5]' }}"
                                        style="height: {{ $bar['percentage'] }}%;"
                                        title="{{ $bar['day_full'] }}: {{ $bar['formatted_amount'] }}"
                                        aria-label="{{ $bar['day_full'] }}: {{ $bar['formatted_amount'] }}"
                                        tabindex="0"
                                    ></div>
                                </div>
                                {{-- Day Label --}}
                                <span class="text-xs font-medium text-[#78716C] mt-3 group-hover:text-[#1C1917] transition-colors">
                                    {{ $bar['day'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-admin.panel>
        </div>

        {{-- Panel Stok Menipis --}}
        <div class="lg:col-span-1">
            <x-admin.panel title="Stok Menipis" icon="alert-triangle" iconClass="text-[#B87A33]">
                <div class="divide-y divide-[#F3EFEA] flex-1">
                    @foreach ($lowStock as $item)
                        <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <x-admin.thumbnail size="w-9 h-9" />
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-medium text-[#1C1917] truncate max-w-[150px] sm:max-w-[170px]" title="{{ $item['name'] }}">
                                        {{ $item['name'] }}
                                    </p>
                                    <p class="text-[11px] text-[#78716C] mt-0.5">
                                        {{ $item['variant'] }}
                                    </p>
                                </div>
                            </div>

                            <x-admin.status-badge
                                :variant="$item['status_variant']"
                                :label="$item['status_label']"
                            />
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 mt-2 border-t border-[#F3EFEA]">
                    <a href="#" class="inline-block text-xs font-medium text-[#1C1917] underline underline-offset-4 hover:text-[#78716C] transition-colors">
                        Kelola inventori
                    </a>
                </div>
            </x-admin.panel>
        </div>
    </div>

    {{-- Row 3: Recent Transactions + Top Products --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Panel Transaksi Terbaru (lebar ~7/12) --}}
        <div class="lg:col-span-7">
            <x-admin.panel title="Transaksi Terbaru">
                <x-slot:headerRight>
                    <a href="#" class="text-xs font-medium text-[#78716C] hover:text-[#1C1917] transition-colors">
                        Lihat semua
                    </a>
                </x-slot:headerRight>

                <div class="divide-y divide-[#F3EFEA] flex-1">
                    @foreach ($recentTransactions as $tx)
                        <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#1C1917] truncate">
                                    {{ $tx['customer_name'] }}
                                </p>
                                <p class="text-xs text-[#78716C] mt-0.5">
                                    {{ $tx['invoice_code'] }}
                                </p>
                            </div>

                            <div class="text-right shrink-0 flex flex-col items-end gap-1">
                                <span class="text-sm font-semibold text-[#1C1917]">
                                    {{ $tx['formatted_amount'] }}
                                </span>
                                <x-admin.status-badge
                                    :variant="$tx['status_variant']"
                                    :label="$tx['status_label']"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-admin.panel>
        </div>

        {{-- Panel Produk Terlaris (lebar ~5/12) --}}
        <div class="lg:col-span-5">
            <x-admin.panel title="Produk Terlaris">
                <div class="divide-y divide-[#F3EFEA] flex-1">
                    @foreach ($topProducts as $product)
                        <div class="py-3 first:pt-0 last:pb-0 flex items-center gap-3">
                            <span class="font-serif text-lg font-semibold text-[#A8A29E] w-5 text-center shrink-0">
                                {{ $product['rank'] }}
                            </span>

                            <x-admin.thumbnail size="w-10 h-10" />

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-[#1C1917] truncate" title="{{ $product['name'] }}">
                                    {{ $product['name'] }}
                                </p>
                                <p class="text-xs text-[#78716C] mt-0.5">
                                    {{ $product['formatted_sales'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-admin.panel>
        </div>
    </div>
</div>
@endsection
