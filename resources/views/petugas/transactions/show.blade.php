@extends('layouts.petugas')

@section('title', 'Detail Pesanan Petugas - Dasbor Modeva')

@section('content')
@php
    $latestPayment = $order->payments->sortByDesc('id')->first();
    $shippingAddress = $order->shipping_address ?: ($order->user?->customer?->address ?? 'Alamat pengiriman belum tersedia.');
    $billingAddress = $order->user?->customer?->address ?: $shippingAddress;
    $shippingService = $order->shipping_service ?: 'Belum ditentukan';
    $actionHint = $order->status === \App\Models\Order::STATUS_VERIFIKASI_PEMBAYARAN
        ? 'Pesanan menunggu verifikasi bukti pembayaran.'
        : 'Pembayaran telah terkonfirmasi. Pesanan siap dikemas dan diproses untuk pengiriman.';
@endphp

<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Detail Pesanan"
        :subtitle="'Rincian lengkap transaksi ' . $order->order_number"
    >
        <x-slot:action>
            <x-admin.button :href="route('petugas.transactions.index')" variant="secondary" icon="arrow-left">
                Kembali ke Daftar
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    {{-- Banner Status Pesanan Saat Ini --}}
    <div class="rounded-2xl bg-[#FAF2E8] border border-[#ECD9C3] p-4 text-sm text-[#8C5E28] flex items-start gap-3">
        <x-icon name="alert-triangle" class="w-5 h-5 text-[#B87A33] shrink-0 mt-0.5" strokeWidth="2" />
        <div>
            <p class="font-semibold text-[#1C1917]">Status Pesanan Saat Ini: {{ $order->status_label }}</p>
            <p class="text-xs text-[#78716C] mt-0.5">{{ $actionHint }}</p>
        </div>
    </div>

    {{-- Ringkasan Nomor Transaksi, Tanggal Pesanan, Total Pembayaran, dan Status --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-[#F3EFEA]">
            <div>
                <p class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Nomor Transaksi</p>
                <h2 class="font-serif text-2xl font-semibold text-[#1C1917] mt-0.5">
                    {{ $order->order_number }}
                </h2>
            </div>

            <x-admin.status-badge
                :variant="$order->status_variant"
                :label="$order->status_label"
            />
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-4 gap-6 pt-5 text-sm">
            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Tanggal Pesanan</dt>
                <dd class="font-medium text-[#1C1917] mt-1">
                    {{ $order->created_at?->format('d M Y, H:i') ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Jasa Pengiriman</dt>
                <dd class="font-medium text-[#1C1917] mt-1">
                    {{ $shippingService }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Jumlah Item</dt>
                <dd class="font-medium text-[#1C1917] mt-1">
                    {{ $order->items->sum('quantity') }} produk
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Total Pembayaran</dt>
                <dd class="font-bold text-lg text-[#1C1917] mt-0.5">
                    {{ $order->formatted_total }}
                </dd>
            </div>
        </dl>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Kolom Kiri: Daftar Produk yang Dibeli & Pengiriman (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">
            {{-- Panel Daftar Produk --}}
            <div class="bg-white rounded-3xl border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
                <div class="px-6 sm:px-7 py-5 border-b border-[#F3EFEA] flex items-center justify-between">
                    <h3 class="font-serif text-xl font-semibold text-[#1C1917]">Daftar Produk yang Dibeli</h3>
                    <span class="text-xs text-[#78716C]">{{ $order->items->count() }} item</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#EFECE6] bg-[#FAF8F5]/60">
                                <th scope="col" class="px-6 py-3 text-[11px] font-semibold tracking-wider text-[#78716C] uppercase">Produk</th>
                                <th scope="col" class="px-4 py-3 text-[11px] font-semibold tracking-wider text-[#78716C] uppercase">Warna</th>
                                <th scope="col" class="px-4 py-3 text-[11px] font-semibold tracking-wider text-[#78716C] uppercase">Ukuran</th>
                                <th scope="col" class="px-4 py-3 text-[11px] font-semibold tracking-wider text-[#78716C] uppercase text-right">Harga</th>
                                <th scope="col" class="px-4 py-3 text-[11px] font-semibold tracking-wider text-[#78716C] uppercase text-center">Jumlah</th>
                                <th scope="col" class="px-6 py-3 text-[11px] font-semibold tracking-wider text-[#78716C] uppercase text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F3EFEA] text-sm text-[#1C1917]">
                            @forelse ($order->items as $item)
                                <tr class="hover:bg-[#FAF8F5]/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-[#1C1917]">
                                            {{ $item->product_name }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-xs sm:text-sm text-[#1C1917] whitespace-nowrap">
                                        {{ $item->color }}
                                    </td>
                                    <td class="px-4 py-4 text-xs sm:text-sm text-[#1C1917] whitespace-nowrap">
                                        {{ $item->size }}
                                    </td>
                                    <td class="px-4 py-4 text-xs sm:text-sm text-[#78716C] text-right whitespace-nowrap">
                                        {{ $item->formatted_price }}
                                    </td>
                                    <td class="px-4 py-4 text-xs sm:text-sm font-medium text-[#1C1917] text-center whitespace-nowrap">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-[#1C1917] text-right whitespace-nowrap">
                                        {{ $item->formatted_subtotal }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-xs text-[#78716C]">
                                        Belum ada rincian produk pada pesanan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-[#EFECE6] bg-[#FAF8F5]/60">
                                <td colspan="5" class="px-6 py-4 text-sm font-semibold text-[#1C1917] text-right">
                                    Total Pembayaran
                                </td>
                                <td class="px-6 py-4 text-base font-bold text-[#1C1917] text-right whitespace-nowrap">
                                    {{ $order->formatted_total }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Panel Data Pelanggan & Pengiriman --}}
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-5">
                <div class="pb-3 border-b border-[#F3EFEA]">
                    <h3 class="font-serif text-xl font-semibold text-[#1C1917]">Data Pelanggan &amp; Pengiriman</h3>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Nama Pelanggan</dt>
                        <dd class="font-medium text-[#1C1917] mt-1">
                            {{ $order->user?->name ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Alamat Email</dt>
                        <dd class="font-medium text-[#1C1917] mt-1">
                            {{ $order->user?->email ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Nomor Telepon</dt>
                        <dd class="font-medium text-[#1C1917] mt-1">
                            {{ $order->user?->customer?->phone ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Jasa Pengiriman</dt>
                        <dd class="font-medium text-[#1C1917] mt-1">
                            {{ $shippingService }}
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Alamat Pengiriman</dt>
                        <dd class="text-sm text-[#1C1917] mt-1.5 leading-relaxed bg-[#FAF8F5] rounded-2xl p-4 border border-[#EFECE6]">
                            {{ $shippingAddress }}
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Alamat Pembayaran / Penagihan</dt>
                        <dd class="text-sm text-[#1C1917] mt-1.5 leading-relaxed bg-[#FAF8F5] rounded-2xl p-4 border border-[#EFECE6]">
                            {{ $billingAddress }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Kolom Kanan: Informasi Pembayaran & Bukti Transfer (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Panel Informasi Pembayaran (Read-only) --}}
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-[#F3EFEA]">
                    <h3 class="font-serif text-xl font-semibold text-[#1C1917]">Informasi Pembayaran</h3>
                    @if ($latestPayment)
                        <x-admin.status-badge
                            :variant="$latestPayment->status_variant"
                            :label="$latestPayment->status_label"
                        />
                    @endif
                </div>

                @if ($latestPayment)
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-xs text-[#78716C]">Metode Pembayaran</dt>
                            <dd class="font-medium text-[#1C1917] mt-0.5 capitalize">
                                {{ $latestPayment->method }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-[#78716C]">Bank</dt>
                            <dd class="font-medium text-[#1C1917] mt-0.5">
                                {{ $latestPayment->bank_name ?: '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-[#78716C]">Nama Rekening Pengirim</dt>
                            <dd class="font-medium text-[#1C1917] mt-0.5">
                                {{ $latestPayment->sender_name ?: '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-[#78716C]">Nominal Dibayar</dt>
                            <dd class="font-bold text-[#1C1917] mt-0.5">
                                {{ $latestPayment->formatted_amount }}
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-xs text-[#78716C]">Waktu Bukti Diunggah</dt>
                            <dd class="font-medium text-[#1C1917] mt-0.5">
                                {{ $latestPayment->uploaded_at?->format('d M Y, H:i') ?? '-' }}
                            </dd>
                        </div>
                    </dl>

                    @if ($latestPayment->verifier || $latestPayment->rejection_reason)
                        <div class="pt-3 border-t border-[#F3EFEA] text-xs space-y-1.5 bg-[#FAF8F5] p-3.5 rounded-xl">
                            @if ($latestPayment->verifier)
                                <p class="text-[#78716C]">
                                    Diverifikasi oleh: <span class="font-medium text-[#1C1917]">{{ $latestPayment->verifier->name }}</span>
                                    @if ($latestPayment->verified_at)
                                        pada {{ $latestPayment->verified_at->format('d M Y, H:i') }}
                                    @endif
                                </p>
                            @endif

                            @if ($latestPayment->rejection_reason)
                                <div>
                                    <span class="font-semibold text-rose-700">Alasan Penolakan:</span>
                                    <p class="text-rose-900 mt-0.5 italic">
                                        "{{ $latestPayment->rejection_reason }}"
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="p-6 text-center text-[#78716C] bg-[#FAF8F5] rounded-2xl border border-dashed border-[#D0C5B8] space-y-1.5">
                        <x-icon name="credit-card" class="w-7 h-7 text-[#A8A29E] mx-auto" strokeWidth="1.5" />
                        <p class="text-xs font-medium text-[#1C1917]">Belum ada data pembayaran</p>
                        <p class="text-[11px] text-[#78716C]">Pelanggan belum melakukan konfirmasi pembayaran untuk pesanan ini.</p>
                    </div>
                @endif
            </div>

            {{-- Panel Bukti Transfer --}}
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-4">
                <h3 class="font-serif text-xl font-semibold text-[#1C1917] pb-3 border-b border-[#F3EFEA]">Bukti Transfer</h3>

                @if ($latestPayment && $latestPayment->proof_path && Route::has('admin.payments.proof'))
                    <div class="space-y-3">
                        <div class="relative rounded-2xl overflow-hidden border border-[#EFECE6] bg-[#FAF8F5] flex items-center justify-center p-2 min-h-56">
                            <img
                                src="{{ route('admin.payments.proof', $latestPayment) }}"
                                alt="Bukti Transfer {{ $order->order_number }}"
                                class="max-h-72 w-auto object-contain rounded-xl"
                            >
                        </div>
                        <div class="text-center">
                            <a
                                href="{{ route('admin.payments.proof', $latestPayment) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-[#1C1917] hover:underline"
                            >
                                <x-icon name="eye" class="w-3.5 h-3.5" strokeWidth="2" />
                                Buka bukti ukuran penuh di tab baru
                            </a>
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center text-[#78716C] bg-[#FAF8F5] rounded-2xl border border-dashed border-[#D0C5B8] space-y-2">
                        <x-icon name="alert-triangle" class="w-8 h-8 text-[#A8A29E] mx-auto" strokeWidth="1.5" />
                        <p class="text-xs font-medium text-[#1C1917]">Bukti transfer belum tersedia</p>
                        <p class="text-[11px] text-[#78716C]">Pelanggan belum mengunggah file struk atau tangkapan layar transfer.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
