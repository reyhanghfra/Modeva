@extends('layouts.admin')

@section('title', 'Detail Transaksi - Dasbor Modeva')

@section('content')
@php
    $latestPayment = $order->payments->sortByDesc('id')->first();
    $shippingAddress = $order->shipping_address ?: ($order->user?->customer?->address ?? 'Alamat pengiriman belum tersedia.');
@endphp

<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Detail Transaksi"
        :subtitle="'Rincian pesanan ' . $order->order_number"
    >
        <x-slot:action>
            <x-admin.button :href="route('admin.transactions.index')" variant="secondary" icon="arrow-left">
                Kembali ke Daftar
            </x-admin.button>
        </x-slot:action>
    </x-admin.page-header>

    <x-admin.flash />

    {{-- Ringkasan Transaksi --}}
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

        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-5 text-sm">
            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Tanggal Transaksi</dt>
                <dd class="font-medium text-[#1C1917] mt-1">
                    {{ $order->created_at?->format('d M Y, H:i') ?? '-' }}
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
        {{-- Kolom Kiri: Daftar Produk Pesanan (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-3xl border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
                <div class="px-6 sm:px-7 py-5 border-b border-[#F3EFEA] flex items-center justify-between">
                    <h3 class="font-serif text-xl font-semibold text-[#1C1917]">Informasi Produk</h3>
                    <span class="text-xs text-[#78716C]">{{ $order->items->count() }} item</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[#EFECE6] bg-[#FAF8F5]/60">
                                <th scope="col" class="px-6 py-3 text-[11px] font-semibold tracking-wider text-[#78716C] uppercase">Produk</th>
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
                                        @if ($item->variant)
                                            <div class="text-xs text-[#78716C] mt-0.5">
                                                {{ $item->variant }}
                                            </div>
                                        @endif
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
                                    <td colspan="4" class="px-6 py-8 text-center text-xs text-[#78716C]">
                                        Belum ada rincian produk pada transaksi ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-[#EFECE6] bg-[#FAF8F5]/60">
                                <td colspan="3" class="px-6 py-4 text-sm font-semibold text-[#1C1917] text-right">
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
        </div>

        {{-- Kolom Kanan: Alamat Pengiriman & Informasi Pembayaran (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Panel Pelanggan & Alamat Pengiriman --}}
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-4">
                <div class="pb-3 border-b border-[#F3EFEA]">
                    <h3 class="font-serif text-xl font-semibold text-[#1C1917]">Alamat Pengiriman</h3>
                </div>

                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Penerima / Customer</dt>
                        <dd class="font-medium text-[#1C1917] mt-1">
                            {{ $order->user?->name ?? '-' }}
                        </dd>
                        @if ($order->user?->email)
                            <dd class="text-xs text-[#78716C] mt-0.5">{{ $order->user->email }}</dd>
                        @endif
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Nomor Telepon</dt>
                        <dd class="font-medium text-[#1C1917] mt-1">
                            {{ $order->user?->customer?->phone ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Alamat Tujuan</dt>
                        <dd class="text-sm text-[#1C1917] mt-1 leading-relaxed bg-[#FAF8F5] rounded-2xl p-4 border border-[#EFECE6]">
                            {{ $shippingAddress }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Panel Informasi Pembayaran --}}
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
                            <dt class="text-xs text-[#78716C]">Nama Pengirim</dt>
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
                                    Diproses oleh: <span class="font-medium text-[#1C1917]">{{ $latestPayment->verifier->name }}</span>
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

                    @if (Route::has('admin.payments.show'))
                        <div class="pt-2">
                            <x-admin.button :href="route('admin.payments.show', $latestPayment)" variant="outline" size="sm" class="w-full">
                                Buka Halaman Verifikasi Pembayaran
                            </x-admin.button>
                        </div>
                    @endif
                @else
                    <div class="p-6 text-center text-[#78716C] bg-[#FAF8F5] rounded-2xl border border-dashed border-[#D0C5B8] space-y-1.5">
                        <x-icon name="credit-card" class="w-7 h-7 text-[#A8A29E] mx-auto" strokeWidth="1.5" />
                        <p class="text-xs font-medium text-[#1C1917]">Belum ada data pembayaran</p>
                        <p class="text-[11px] text-[#78716C]">Pelanggan belum melakukan konfirmasi atau unggah bukti pembayaran.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
