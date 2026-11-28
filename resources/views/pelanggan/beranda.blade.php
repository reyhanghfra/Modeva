@extends('layouts.customer')

@section('title', 'Modeva - Fashion Store')

@section('content')
@php
    $src = fn ($p) => $p && \Illuminate\Support\Facades\Storage::disk('public')->exists($p)
        ? \Illuminate\Support\Facades\Storage::url($p)
        : null;
    $catalog = Route::has('katalog.index') ? route('katalog.index') : (Route::has('products.index') ? route('products.index') : '#');
    $wrap = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8';
@endphp

{{-- Hero --}}
<section class="{{ $wrap }} pt-8">
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#F3EFEA] to-[#E8E0D5] min-h-[520px] lg:min-h-[640px] flex items-center">
        @if ($img = $src($photos['hero'] ?? null))
            <img src="{{ $img }}" alt="" class="absolute inset-y-0 right-0 w-full lg:w-1/2 h-full object-cover opacity-30 lg:opacity-100">
        @endif
        <div class="relative p-8 sm:p-12 lg:p-20 max-w-2xl">
            <p class="text-xs tracking-[0.3em] uppercase text-[#78716C]">Koleksi Terbaru {{ date('Y') }}</p>
            <h1 class="font-serif text-5xl sm:text-7xl lg:text-8xl leading-[0.95] mt-6">
                Sederhana,<br><em class="text-[#A89480]">berkelas</em>, abadi.
            </h1>
            <p class="mt-8 max-w-md text-[#57534E] leading-relaxed">Linen, wol, dan kulit pilihan dalam palet netral yang mudah dipadukan untuk keseharian Anda.</p>
            <div class="mt-10 flex flex-wrap gap-3">
                <a href="{{ $catalog }}" class="px-7 py-3.5 rounded-full bg-[#222222] text-white text-sm font-medium hover:bg-black transition-colors">Belanja Sekarang</a>
                <a href="#kategori" class="px-7 py-3.5 rounded-full border border-[#D0C5B8] text-sm font-medium hover:bg-white/60 transition-colors">Lihat Kategori</a>
            </div>
        </div>
    </div>
</section>

{{-- Keunggulan --}}
<section class="{{ $wrap }} mt-5 grid gap-4 md:grid-cols-3">
    @foreach ([['box', 'Pengiriman JNE, J&T, Lion Parcel'], ['credit-card', 'Pembayaran aman via transfer bank'], ['check', 'Stok real-time, tanpa kehabisan di kasir']] as [$icon, $text])
        <div class="flex items-center gap-4 rounded-2xl bg-white/70 px-6 py-5 text-sm text-[#57534E]">
            <x-icon :name="$icon" class="w-5 h-5 text-[#A89480] shrink-0" />
            <span>{{ $text }}</span>
        </div>
    @endforeach
</section>

{{-- Kategori --}}
@if ($categories->isNotEmpty())
<section id="kategori" class="{{ $wrap }} mt-24 scroll-mt-28">
    <p class="text-xs tracking-[0.3em] uppercase text-[#A89480]">Kategori Pilihan</p>
    <h2 class="font-serif text-4xl sm:text-5xl mt-2 mb-10">Belanja berdasarkan kategori</h2>
    <div class="grid gap-5 md:grid-cols-3">
        @foreach ($categories as $c)
            <a href="{{ $catalog }}" class="group relative block aspect-[3/4] rounded-[2rem] overflow-hidden bg-[#D9CFC2]">
                @if ($img = $src($c->image))
                    <img src="{{ $img }}" alt="{{ $c->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                <div class="absolute bottom-0 p-7 text-white">
                    <h3 class="font-serif text-3xl">{{ $c->name }}</h3>
                    @if ($c->description)
                        <p class="text-sm text-white/80 mt-1 line-clamp-2">{{ $c->description }}</p>
                    @endif
                    <span class="inline-flex items-center gap-1 mt-4 text-sm font-medium">Jelajahi <x-icon name="chevron-right" class="w-4 h-4" /></span>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- Kedatangan Terbaru --}}
@if ($newest->isNotEmpty())
<section class="{{ $wrap }} mt-24">
    <div class="flex items-end justify-between gap-4 mb-10">
        <div>
            <p class="text-xs tracking-[0.3em] uppercase text-[#A89480]">Baru Hadir</p>
            <h2 class="font-serif text-4xl sm:text-5xl mt-2">Kedatangan Terbaru</h2>
        </div>
        <a href="{{ $catalog }}" class="inline-flex items-center gap-1 text-sm underline underline-offset-4">Lihat semua <x-icon name="chevron-right" class="w-4 h-4" /></a>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
        @foreach ($newest as $p)
            <x-product-card :product="$p" :href="$catalog" :new="true" />
        @endforeach
    </div>
</section>
@endif

{{-- Banner Promo --}}
<section class="{{ $wrap }} mt-24">
    <div class="grid md:grid-cols-2 rounded-[2rem] overflow-hidden bg-[#ECE6DD]">
        <div class="p-8 sm:p-12 lg:p-16 flex flex-col justify-center">
            <p class="text-xs tracking-[0.3em] uppercase text-[#A89480]">Penawaran Terbatas</p>
            <h2 class="font-serif text-4xl sm:text-6xl leading-tight mt-4">Diskon 15% pembelian pertama</h2>
            <p class="mt-6 max-w-md text-[#57534E] leading-relaxed">
                Gunakan kode <span class="px-2 py-0.5 rounded-md bg-white font-medium tracking-widest">SELAMATDATANG</span>
                saat menghubungi kami via WhatsApp untuk pesanan pertama Anda.
            </p>
            <div class="mt-8">
                <a href="{{ $catalog }}" class="inline-block px-7 py-3.5 rounded-full bg-[#222222] text-white text-sm font-medium hover:bg-black transition-colors">Mulai Belanja</a>
            </div>
        </div>
        <div class="relative min-h-[320px] bg-[#D9BFA0]">
            @if ($img = $src($photos['promo'] ?? null))
                <img src="{{ $img }}" alt="" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
            @endif
        </div>
    </div>
</section>

{{-- Produk Terlaris --}}
@if ($bestSellers->isNotEmpty())
<section class="{{ $wrap }} mt-24">
    <div class="flex items-end justify-between gap-4 mb-10">
        <div>
            <p class="text-xs tracking-[0.3em] uppercase text-[#A89480]">Paling Dicari</p>
            <h2 class="font-serif text-4xl sm:text-5xl mt-2">Produk Terlaris</h2>
        </div>
        <a href="{{ $catalog }}" class="inline-flex items-center gap-1 text-sm underline underline-offset-4">Lihat semua <x-icon name="chevron-right" class="w-4 h-4" /></a>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
        @foreach ($bestSellers as $p)
            <x-product-card :product="$p" :href="$catalog" />
        @endforeach
    </div>
</section>
@endif
@endsection