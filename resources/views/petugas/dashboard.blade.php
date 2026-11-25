@extends('layouts.petugas')

@section('title', 'Dasbor Operasional Petugas - Modeva')

@section('content')
@php
    $transactionsUrl = Route::has('petugas.transactions.index')
        ? route('petugas.transactions.index')
        : (Route::has('admin.transactions.index') ? route('admin.transactions.index') : '#');
@endphp

<div class="space-y-8">
    {{-- Page Header --}}
    <x-admin.page-header
        title="Dasbor Operasional"
        subtitle="Ringkasan status pesanan dan transaksi yang membutuhkan tindak lanjut petugas"
    >
        <x-slot:action>
            <x-admin.button :href="$transactionsUrl" variant="primary" icon="receipt">
                Data Transaksi
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    {{-- Row 1: 4 Stat Cards Ringkasan Status Pesanan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-admin.stat-card
            label="Menunggu Pembayaran"
            :value="$summary['menunggu_pembayaran']"
            icon="receipt"
            subtext="Menunggu pelunasan pelanggan"
            subtextClass="text-[#78716C]"
        />

        <x-admin.stat-card
            label="Sedang Diproses"
            :value="$summary['diproses']"
            icon="box"
            subtext="Siap dikemas oleh petugas"
            subtextClass="text-[#8C5E28]"
        />

        <x-admin.stat-card
            label="Dikirim"
            :value="$summary['dikirim']"
            icon="layers"
            subtext="Dalam perjalanan ke pelanggan"
            subtextClass="text-[#8C5E28]"
        />

        <x-admin.stat-card
            label="Selesai"
            :value="$summary['selesai']"
            icon="check-circle"
            subtext="Pesanan telah diterima"
            subtextClass="text-emerald-700"
        />
    </div>

    {{-- Row 2: Transaksi Butuh Tindakan + Daftar Pesanan Terbaru --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Panel Transaksi yang Membutuhkan Tindakan Petugas (7 cols) --}}
        <div class="lg:col-span-7">
            <x-admin.panel
                title="Transaksi Membutuhkan Tindakan"
                icon="alert-triangle"
                iconClass="text-[#B87A33]"
            >
                <x-slot:headerRight>
                    <a href="{{ $transactionsUrl }}" class="text-xs font-medium text-[#78716C] hover:text-[#1C1917] transition-colors">
                        Buka Data Transaksi
                    </a>
                </x-slot:headerRight>

                <div class="mb-4 flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FAF2E8] text-[#8C5E28] border border-[#ECD9C3] font-medium">
                        {{ $pendingVerificationCount }} pembayaran menunggu verifikasi
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F2EFE9] text-[#1C1917] border border-[#E5DDD2] font-medium">
                        {{ $summary['diproses'] }} pesanan siap diproses / dikirim
                    </span>
                </div>

                <div class="divide-y divide-[#F3EFEA] flex-1">
                    @forelse ($actionRequiredOrders as $order)
                        @php
                            $actionNote = $order->status === \App\Models\Order::STATUS_VERIFIKASI_PEMBAYARAN
                                ? 'Perlu verifikasi bukti pembayaran'
                                : 'Perlu pengemasan dan pengiriman';
                        @endphp
                        <div class="py-3.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-semibold text-[#1C1917]">
                                        {{ $order->order_number }}
                                    </span>
                                    <span class="text-xs text-[#78716C]">&middot;</span>
                                    <span class="text-sm font-medium text-[#1C1917] truncate">
                                        {{ $order->user?->name ?? '-' }}
                                    </span>
                                </div>
                                <p class="text-xs text-[#8C5E28] font-medium mt-1">
                                    {{ $actionNote }}
                                    @if ($order->latestPayment?->bank_name)
                                        <span class="text-[#78716C] font-normal">
                                            &middot; {{ ucfirst($order->latestPayment->method) }} ({{ $order->latestPayment->bank_name }})
                                        </span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex sm:flex-col items-center sm:items-end justify-between gap-1 shrink-0">
                                <span class="text-sm font-semibold text-[#1C1917]">
                                    {{ $order->formatted_total }}
                                </span>
                                <x-admin.status-badge
                                    :variant="$order->status_variant"
                                    :label="$order->status_label"
                                />
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-[#78716C] space-y-2">
                            <x-icon name="check-circle" class="w-8 h-8 text-emerald-500 mx-auto" strokeWidth="1.5" />
                            <p class="text-sm font-medium text-[#1C1917]">Semua transaksi sudah tertangani</p>
                            <p class="text-xs text-[#78716C]">Belum ada transaksi yang memerlukan tindakan operasional saat ini.</p>
                        </div>
                    @endforelse
                </div>

                <div class="pt-4 mt-4 border-t border-[#F3EFEA]">
                    <a href="{{ $transactionsUrl }}" class="inline-block text-xs font-medium text-[#1C1917] underline underline-offset-4 hover:text-[#78716C] transition-colors">
                        Lihat seluruh data transaksi
                    </a>
                </div>
            </x-admin.panel>
        </div>

        {{-- Panel Daftar Pesanan Terbaru (5 cols) --}}
        <div class="lg:col-span-5">
            <x-admin.panel title="Pesanan Terbaru">
                <x-slot:headerRight>
                    <a href="{{ $transactionsUrl }}" class="text-xs font-medium text-[#78716C] hover:text-[#1C1917] transition-colors">
                        Lihat semua
                    </a>
                </x-slot:headerRight>

                <div class="divide-y divide-[#F3EFEA] flex-1">
                    @forelse ($recentOrders as $order)
                        <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#1C1917] truncate">
                                    {{ $order->user?->name ?? '-' }}
                                </p>
                                <p class="text-xs text-[#78716C] mt-0.5 truncate">
                                    {{ $order->order_number }} &middot; {{ $order->created_at?->format('d M Y, H:i') ?? '-' }}
                                </p>
                            </div>

                            <div class="text-right shrink-0 flex flex-col items-end gap-1">
                                <span class="text-sm font-semibold text-[#1C1917]">
                                    {{ $order->formatted_total }}
                                </span>
                                <x-admin.status-badge
                                    :variant="$order->status_variant"
                                    :label="$order->status_label"
                                />
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-[#78716C] space-y-2">
                            <x-icon name="box" class="w-8 h-8 text-[#A8A29E] mx-auto" strokeWidth="1.5" />
                            <p class="text-sm font-medium text-[#1C1917]">Belum ada pesanan masuk</p>
                            <p class="text-xs text-[#78716C]">Pesanan pelanggan terbaru akan muncul di panel ini.</p>
                        </div>
                    @endforelse
                </div>
            </x-admin.panel>
        </div>
    </div>
</div>
@endsection
