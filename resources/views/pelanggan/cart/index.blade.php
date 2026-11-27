@extends('layouts.store')

@section('title', 'Keranjang Belanja - MODEVA')

@section('content')
@php
    $continueShoppingUrl = Route::has('catalog.index') ? route('catalog.index') : route('home');
    $checkoutUrl = Route::has('checkout.index') ? route('checkout.index') : null;
@endphp

<div class="space-y-8">
    {{-- Page Heading --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-[#E5DDD2]">
        <div>
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-[#8C5E28]">
                Tas Belanja Anda
            </p>
            <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-[#1C1917] tracking-tight mt-1">
                Keranjang Belanja
            </h1>
            <p class="text-sm text-[#78716C] mt-1">
                {{ $totalItems > 0 ? $totalItems . ' produk siap untuk dilanjutkan ke pembayaran' : 'Belum ada produk di dalam keranjang Anda' }}
            </p>
        </div>

        <a
            href="{{ $continueShoppingUrl }}"
            class="inline-flex items-center gap-2 text-xs sm:text-sm font-medium text-[#5C3D2E] hover:text-[#1C1917] underline underline-offset-4 transition-colors self-start sm:self-auto"
        >
            <x-icon name="arrow-left" class="w-4 h-4" strokeWidth="1.75" />
            Lanjutkan Belanja
        </a>
    </div>

    {{-- Flash Notifications & Validation Errors --}}
    <x-admin.flash />

    @if ($errors->any())
        <div class="rounded-2xl bg-rose-50 border border-rose-200/80 p-4 text-sm text-rose-800 space-y-1">
            @foreach ($errors->all() as $error)
                <p class="font-medium">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($cartItems->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Kolom Kiri: Daftar Produk di Keranjang (8 cols) --}}
            <div class="lg:col-span-8 space-y-4">
                @foreach ($cartItems as $item)
                    @php
                        $variant = $item->variant;
                        $product = $variant?->product;
                        $stockAvailable = (int) ($variant?->stock ?? 0);
                        $isOutOfStock = ! $variant || ! $product || ! $product->is_active || $stockAvailable < 1;
                        $exceedsStock = ! $isOutOfStock && $item->quantity > $stockAvailable;
                        $imageUrl = $item->product_image_url;
                    @endphp

                    <article class="bg-white rounded-3xl p-4 sm:p-6 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col sm:flex-row gap-5">
                        {{-- Kiri: Gambar Produk --}}
                        <div class="w-full sm:w-32 h-44 sm:h-40 rounded-2xl overflow-hidden bg-[#EDE7DF] border border-[#E5DDD2] shrink-0 flex items-center justify-center">
                            @if ($imageUrl)
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $product?->name ?? 'Produk Modeva' }}"
                                    class="w-full h-full object-cover"
                                >
                            @else
                                <div class="text-center px-3 space-y-1.5">
                                    <span class="font-serif text-2xl font-semibold tracking-widest text-[#5C3D2E] uppercase block">
                                        {{ mb_substr($product?->name ?? 'M', 0, 2) }}
                                    </span>
                                    <span class="block text-[10px] uppercase tracking-wider text-[#78716C]">
                                        Modeva
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Kanan: Informasi Produk, Jumlah, Harga, & Hapus --}}
                        <div class="flex-1 min-w-0 flex flex-col justify-between gap-4">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                                <div class="min-w-0 space-y-1">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#8C5E28]">
                                        {{ $product?->brand ?? 'MODEVA' }}
                                    </p>
                                    <h2 class="text-base sm:text-lg font-semibold text-[#1C1917] leading-snug">
                                        {{ $product?->name ?? 'Produk tidak tersedia' }}
                                    </h2>

                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 pt-1 text-xs text-[#78716C]">
                                        <span>Ukuran: <strong class="font-medium text-[#1C1917]">{{ $variant?->size ?? '-' }}</strong></span>
                                        <span>Warna: <strong class="font-medium text-[#1C1917]">{{ $variant?->color ?? '-' }}</strong></span>
                                        <span>Stok: <strong class="font-medium {{ $stockAvailable > 0 ? 'text-[#1C1917]' : 'text-rose-600' }}">{{ $stockAvailable }}</strong></span>
                                    </div>
                                </div>

                                <div class="sm:text-right shrink-0">
                                    <p class="text-xs text-[#78716C]">Harga Satuan</p>
                                    <p class="text-sm sm:text-base font-semibold text-[#1C1917] mt-0.5">
                                        {{ $item->formatted_unit_price }}
                                    </p>
                                </div>
                            </div>

                            {{-- Peringatan Stok jika stok menipis/habis --}}
                            @if ($isOutOfStock)
                                <div class="text-xs font-medium text-rose-700 bg-rose-50 border border-rose-200/80 rounded-xl px-3 py-2">
                                    Stok varian ini sedang habis atau produk tidak aktif. Silakan hapus item ini sebelum checkout.
                                </div>
                            @elseif ($exceedsStock)
                                <div class="text-xs font-medium text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                                    Jumlah di keranjang ({{ $item->quantity }}) melebihi stok tersisa ({{ $stockAvailable }}). Kurangi jumlah agar sesuai stok.
                                </div>
                            @endif

                            {{-- Kontrol Jumlah, Subtotal, & Tombol Hapus --}}
                            <div class="pt-3 border-t border-[#F3EFEA] flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-[#78716C] mr-1">Jumlah:</span>

                                    {{-- Kurangi Jumlah --}}
                                    <form method="POST" action="{{ route('cart.update', $item) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="decrease">
                                        <button
                                            type="submit"
                                            class="w-8 h-8 rounded-full border border-[#D0C5B8] bg-[#FAF8F5] hover:bg-[#EDE7DF] text-[#1C1917] inline-flex items-center justify-center transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                                            title="Kurangi jumlah"
                                            aria-label="Kurangi jumlah {{ $product?->name }}"
                                            @if ($item->quantity <= 1) disabled @endif
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                            </svg>
                                        </button>
                                    </form>

                                    <span class="min-w-[2.25rem] text-center text-sm font-semibold text-[#1C1917] select-none">
                                        {{ $item->quantity }}
                                    </span>

                                    {{-- Tambah Jumlah --}}
                                    <form method="POST" action="{{ route('cart.update', $item) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="increase">
                                        <button
                                            type="submit"
                                            class="w-8 h-8 rounded-full border border-[#D0C5B8] bg-[#FAF8F5] hover:bg-[#EDE7DF] text-[#1C1917] inline-flex items-center justify-center transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                                            title="Tambah jumlah"
                                            aria-label="Tambah jumlah {{ $product?->name }}"
                                            @if ($isOutOfStock || $item->quantity >= $stockAvailable) disabled @endif
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <line x1="12" y1="5" x2="12" y2="19" />
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                <div class="flex items-center gap-4 ml-auto">
                                    <div class="text-right">
                                        <span class="block text-[11px] text-[#78716C]">Subtotal</span>
                                        <span class="text-sm sm:text-base font-bold text-[#1C1917]">
                                            {{ $item->formatted_subtotal }}
                                        </span>
                                    </div>

                                    {{-- Tombol Hapus Produk --}}
                                    <form
                                        method="POST"
                                        action="{{ route('cart.destroy', $item) }}"
                                        onsubmit="return confirm('Hapus {{ addslashes($product?->name ?? 'produk ini') }} dari keranjang belanja?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 transition-colors cursor-pointer"
                                            title="Hapus dari keranjang"
                                        >
                                            <x-icon name="trash" class="w-3.5 h-3.5" strokeWidth="2" />
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Kolom Kanan: Ringkasan Belanja (4 cols) --}}
            <aside class="lg:col-span-4 lg:sticky lg:top-28">
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-6">
                    <div class="pb-4 border-b border-[#F3EFEA]">
                        <h2 class="font-serif text-2xl font-semibold text-[#1C1917]">
                            Ringkasan Belanja
                        </h2>
                        <p class="text-xs text-[#78716C] mt-1">
                            Perhitungan otomatis berdasarkan harga dan jumlah terkini
                        </p>
                    </div>

                    <dl class="space-y-3.5 text-sm">
                        <div class="flex items-center justify-between text-[#57534E]">
                            <dt>Total Produk</dt>
                            <dd class="font-medium text-[#1C1917]">{{ $totalItems }} item</dd>
                        </div>

                        <div class="flex items-center justify-between text-[#57534E]">
                            <dt>Subtotal Belanja</dt>
                            <dd class="font-semibold text-[#1C1917]">{{ $formattedSubtotal }}</dd>
                        </div>

                        <div class="pt-4 border-t border-[#F3EFEA] flex items-center justify-between">
                            <dt class="font-semibold text-[#1C1917]">Total Pembayaran</dt>
                            <dd class="font-serif text-2xl font-bold text-[#1C1917]">
                                {{ $formattedTotal }}
                            </dd>
                        </div>
                    </dl>

                    <div class="space-y-3 pt-2">
                        @if ($checkoutUrl && ! $hasStockIssue)
                            <a
                                href="{{ $checkoutUrl }}"
                                class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full text-sm font-medium bg-[#1C1917] hover:bg-[#4A3428] text-[#FAF8F5] transition-colors shadow-2xs"
                            >
                                Lanjut ke Checkout
                            </a>
                        @else
                            <button
                                type="button"
                                @if ($hasStockIssue)
                                    disabled
                                    title="Perbaiki item yang melebihi stok terlebih dahulu"
                                @else
                                    title="Halaman checkout akan tersedia pada tahap berikutnya"
                                @endif
                                class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full text-sm font-medium bg-[#2C221E] hover:bg-[#4A3428] text-[#FAF8F5] transition-colors shadow-2xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                Lanjut ke Checkout
                            </button>
                            @if (! $hasStockIssue && ! $checkoutUrl)
                                <p class="text-[11px] text-center text-[#78716C]">
                                    Proses checkout akan diaktifkan setelah modul checkout tersedia.
                                </p>
                            @endif
                        @endif

                        <a
                            href="{{ $continueShoppingUrl }}"
                            class="w-full inline-flex items-center justify-center px-6 py-2.5 rounded-full text-xs sm:text-sm font-medium bg-[#EDE7DF] hover:bg-[#E2DAD0] text-[#1C1917] border border-[#D0C5B8] transition-colors"
                        >
                            Lanjutkan Belanja
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    @else
        {{-- Tampilan Keranjang Kosong --}}
        <div class="bg-white rounded-3xl p-10 sm:p-16 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] text-center max-w-2xl mx-auto space-y-5">
            <div class="w-16 h-16 rounded-full bg-[#EDE7DF] border border-[#E5DDD2] flex items-center justify-center mx-auto text-[#5C3D2E]">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                    <path d="M3 6h18" />
                    <path d="M16 10a4 4 0 0 1-8 0" />
                </svg>
            </div>

            <div class="space-y-2">
                <h2 class="font-serif text-2xl sm:text-3xl font-semibold text-[#1C1917]">
                    Keranjang Belanja Masih Kosong
                </h2>
                <p class="text-sm text-[#78716C] max-w-md mx-auto">
                    Temukan koleksi pakaian, sepatu, dan tas pilihan MODEVA untuk anak-anak maupun dewasa.
                </p>
            </div>

            <div class="pt-2">
                <a
                    href="{{ $continueShoppingUrl }}"
                    class="inline-flex items-center justify-center px-7 py-3 rounded-full text-sm font-medium bg-[#1C1917] hover:bg-[#4A3428] text-[#FAF8F5] transition-colors"
                >
                    Lanjutkan Belanja
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
