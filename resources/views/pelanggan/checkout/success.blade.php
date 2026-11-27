@extends('layouts.store')

@section('title', 'Pesanan Berhasil Dibuat - MODEVA')

@section('content')
@php
    $payment = $order->latestPayment;
    $continueShoppingUrl = Route::has('catalog.index') ? route('catalog.index') : route('home');
@endphp

<div class="max-w-3xl mx-auto space-y-8">
    {{-- Header Konfirmasi --}}
    <div class="bg-white rounded-3xl p-8 sm:p-10 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] text-center space-y-4">
        <div class="w-14 h-14 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center mx-auto text-emerald-700">
            <x-icon name="check-circle" class="w-7 h-7" strokeWidth="1.75" />
        </div>

        <div class="space-y-1.5">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#8C5E28]">
                Konfirmasi Pesanan
            </p>
            <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-[#1C1917]">
                Pesanan Berhasil Dibuat
            </h1>
            <p class="text-sm text-[#78716C] max-w-lg mx-auto">
                Terima kasih telah berbelanja di MODEVA. Simpan nomor transaksi Anda untuk proses konfirmasi pembayaran dan pelacakan pesanan.
            </p>
        </div>

        <div class="inline-flex flex-wrap items-center justify-center gap-3 pt-2">
            <span class="px-4 py-1.5 rounded-full bg-[#FAF8F5] border border-[#E5DDD2] font-mono text-sm font-semibold text-[#1C1917]">
                {{ $order->order_number }}
            </span>
            <x-admin.status-badge
                :variant="$order->status_variant"
                :label="$order->status_label"
            />
        </div>
    </div>

    <x-admin.flash />

    {{-- Ringkasan Informasi Transaksi & Pembayaran --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-6">
        <div class="pb-4 border-b border-[#F3EFEA] flex items-center justify-between">
            <h2 class="font-serif text-2xl font-semibold text-[#1C1917]">
                Rincian Pesanan &amp; Pembayaran
            </h2>
            <span class="text-xs text-[#78716C]">
                {{ $order->created_at?->format('d M Y, H:i') }}
            </span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Metode &amp; Bank Pembayaran</dt>
                <dd class="font-medium text-[#1C1917] mt-1">
                    @if ($payment)
                        {{ ucfirst($payment->method) }} &mdash; {{ $payment->bank_name }}
                    @else
                        Transfer Bank
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Status Pembayaran</dt>
                <dd class="mt-1">
                    @if ($payment)
                        <x-admin.status-badge
                            :variant="$payment->status_variant"
                            :label="$payment->status_label"
                        />
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Jasa Pengiriman</dt>
                <dd class="font-medium text-[#1C1917] mt-1">
                    {{ $order->shipping_service ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Total Pembayaran</dt>
                <dd class="font-serif text-2xl font-bold text-[#1C1917] mt-0.5">
                    {{ $order->formatted_total }}
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-[#78716C] uppercase tracking-wider">Alamat &amp; Penerima Pengiriman</dt>
                <dd class="text-sm text-[#1C1917] mt-1.5 bg-[#FAF8F5] rounded-2xl p-4 border border-[#EFECE6] leading-relaxed">
                    {{ $order->shipping_address }}
                </dd>
            </div>
        </dl>

        {{-- Daftar Item Pesanan --}}
        <div class="pt-4 border-t border-[#F3EFEA] space-y-3">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-[#78716C]">
                Produk yang Dipesan
            </h3>

            <div class="divide-y divide-[#F3EFEA]">
                @foreach ($order->items as $item)
                    <div class="py-3 flex items-center justify-between gap-4 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium text-[#1C1917]">{{ $item->product_name }}</p>
                            <p class="text-xs text-[#78716C] mt-0.5">
                                {{ $item->variant }} &middot; {{ $item->quantity }} &times; {{ $item->formatted_price }}
                            </p>
                        </div>
                        <span class="font-semibold text-[#1C1917] shrink-0">
                            {{ $item->formatted_subtotal }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Informasi Integrasi Pembayaran Pelanggan --}}
        <div class="rounded-2xl bg-[#FAF2E8] border border-[#ECD9C3] p-4 text-xs text-[#5C3D2E] leading-relaxed">
            <strong>Langkah Pembayaran Selanjutnya:</strong> Silakan lakukan transfer sebesar <strong>{{ $order->formatted_total }}</strong> ke rekening <strong>{{ $payment?->bank_name ?? 'Bank Tujuan' }}</strong> resmi MODEVA. Fitur unggah bukti pembayaran pelanggan akan terhubung pada halaman ini setelah modul pembayaran pelanggan digabungkan.
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-end gap-3">
            <a
                href="{{ route('cart.index') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 rounded-full text-xs sm:text-sm font-medium bg-[#EDE7DF] hover:bg-[#E2DAD0] text-[#1C1917] border border-[#D0C5B8] transition-colors"
            >
                Lihat Keranjang
            </a>
            <a
                href="{{ $continueShoppingUrl }}"
                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 rounded-full text-xs sm:text-sm font-medium bg-[#1C1917] hover:bg-[#4A3428] text-[#FAF8F5] transition-colors"
            >
                Lanjutkan Belanja
            </a>
        </div>
    </div>
</div>
@endsection
