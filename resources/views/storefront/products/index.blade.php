@extends('layouts.customer')

@section('title', 'Katalog Fashion - MODEVA')
@section('meta_description', 'Jelajahi koleksi pakaian, sepatu, dan tas pilihan MODEVA.')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <div class="border-b border-[#E7E0D7] pb-7 sm:pb-9">
        <p class="text-[10px] font-medium uppercase tracking-[0.3em] text-[#8A8178]">Temukan pilihanmu</p>
        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-serif text-5xl text-[#1C1917] sm:text-6xl">Koleksi</h1>
                <p class="mt-2 text-sm text-[#78716C]">{{ $products->total() }} produk pilihan MODEVA</p>
            </div>
            <a href="{{ route('home') }}" class="text-xs font-medium text-[#57534E] underline decoration-[#D0C5B8] underline-offset-4 hover:text-[#1C1917]">
                Kembali ke beranda
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 gap-3 py-5 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_13rem_13rem_auto]">
        <label>
            <span class="sr-only">Cari nama, merek, atau SKU produk</span>
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nama, merek, atau SKU..."
                class="w-full rounded-xl border border-[#E5DDD2] bg-white px-4 py-3 text-sm text-[#1C1917] placeholder-[#A8A29E] focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]"
            >
        </label>
        <label>
            <span class="sr-only">Filter kategori</span>
            <select name="category_id" class="w-full rounded-xl border border-[#E5DDD2] bg-white px-4 py-3 text-sm text-[#1C1917] focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="sr-only">Urutkan produk</span>
            <select name="sort" class="w-full rounded-xl border border-[#E5DDD2] bg-white px-4 py-3 text-sm text-[#1C1917] focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]">
                <option value="latest" @selected($sort === 'latest')>Terbaru</option>
                <option value="price_asc" @selected($sort === 'price_asc')>Harga Terendah</option>
                <option value="price_desc" @selected($sort === 'price_desc')>Harga Tertinggi</option>
            </select>
        </label>
        <button type="submit" class="rounded-xl bg-[#1F1F1E] px-5 py-3 text-sm font-medium text-white transition-colors hover:bg-[#33312E]">
            Terapkan
        </button>
    </form>

    @if ($products->isNotEmpty())
        <div class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2 sm:gap-x-5 lg:grid-cols-3 lg:gap-x-7 xl:grid-cols-4">
            @foreach ($products as $product)
                @include('storefront.products.partials.card', ['product' => $product])
            @endforeach
        </div>

        <div class="mt-10 border-t border-[#E7E0D7] pt-6">
            {{ $products->links() }}
        </div>
    @else
        <div class="rounded-3xl border border-dashed border-[#D0C5B8] bg-white px-5 py-16 text-center">
            <p class="font-serif text-3xl text-[#1C1917]">Belum ada produk yang cocok</p>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#78716C]">Coba kata kunci lain atau hapus sebagian filter untuk melihat lebih banyak koleksi.</p>
            <a href="{{ route('products.index') }}" class="mt-6 inline-flex rounded-full border border-[#D0C5B8] px-5 py-2.5 text-sm font-medium text-[#1C1917] hover:bg-[#F7F4F0]">
                Lihat semua produk
            </a>
        </div>
    @endif
</div>
@endsection
