@extends('layouts.storefront')

@section('title', 'MODEVA - Koleksi Fashion Pilihan')
@section('meta_description', 'Temukan koleksi fashion MODEVA dengan potongan timeless dan palet warna natural.')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <section class="relative overflow-hidden rounded-[2rem] border border-[#E7E0D7] bg-[#EDE7DF] px-6 py-16 text-center sm:px-12 sm:py-24">
        <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full border border-white/60 sm:h-96 sm:w-96"></div>
        <div class="absolute -bottom-36 -left-20 h-72 w-72 rounded-full border border-white/60"></div>
        <div class="relative mx-auto max-w-2xl">
            <p class="text-[10px] font-medium uppercase tracking-[0.35em] text-[#78716C] sm:text-xs">Fashion, thoughtfully chosen</p>
            <h1 class="mt-5 font-serif text-5xl leading-[0.95] text-[#1C1917] sm:text-7xl">Ketenangan dalam<br class="hidden sm:block"> setiap pilihan.</h1>
            <p class="mx-auto mt-6 max-w-lg text-sm leading-7 text-[#57534E] sm:text-base">
                Temukan siluet yang timeless, tekstur yang terasa istimewa, dan palet netral untuk menemani keseharianmu.
            </p>
            <a href="{{ route('products.index') }}" class="mt-8 inline-flex items-center justify-center rounded-full bg-[#1F1F1E] px-7 py-3.5 text-sm font-medium text-white transition-colors hover:bg-[#33312E]">
                Jelajahi Koleksi
            </a>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 py-10 sm:grid-cols-3 sm:py-14" aria-label="Kategori koleksi">
        @foreach (['Pakaian', 'Sepatu', 'Tas'] as $categoryName)
            <a
                href="{{ route('products.index', ['category' => \Illuminate\Support\Str::slug($categoryName)]) }}"
                class="group flex min-h-36 items-end justify-between rounded-2xl border border-[#EFECE6] bg-white p-5 transition-colors hover:bg-[#F7F4F0] sm:p-6"
            >
                <div>
                    <p class="text-[10px] uppercase tracking-[0.22em] text-[#A8A29E]">Koleksi MODEVA</p>
                    <h2 class="mt-2 font-serif text-3xl text-[#1C1917]">{{ $categoryName }}</h2>
                </div>
                <span class="text-xl text-[#A8A29E] transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
            </a>
        @endforeach
    </section>
</div>
@endsection
