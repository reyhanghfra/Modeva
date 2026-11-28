@extends('layouts.customer')

@section('title', $product->name.' - MODEVA')
@section('meta_description', \Illuminate\Support\Str::limit($product->description, 155))

@section('content')
@php
    $hasAvailableStock = $product->variants->contains(fn ($variant) => $variant->stock > 0);
@endphp

<div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
    <nav class="mb-6 flex flex-wrap items-center gap-2 text-xs text-[#8A8178]" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#1C1917]">Beranda</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-[#1C1917]">{{ $product->category->name }}</a>
        <span aria-hidden="true">/</span>
        <span class="text-[#57534E]" aria-current="page">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-14">
        <div class="overflow-hidden rounded-[1.75rem] bg-[#EDE7DF]">
            <div class="aspect-[4/5]">
                @if ($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')">
                    <div role="img" aria-label="Foto produk belum tersedia" class="hidden h-full w-full items-center justify-center bg-gradient-to-br from-[#F2EEE8] to-[#DED5C9] font-serif text-2xl tracking-[0.2em] text-[#A89B8A]">
                        MODEVA
                    </div>
                @else
                    <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-[#F2EEE8] to-[#DED5C9] text-[#A89B8A]">
                        <svg class="h-16 w-16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.9" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5 8.5 12l3 3 2-2 6.5 6.5M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm4 5h.01"/>
                        </svg>
                        <span class="mt-4 font-serif text-2xl tracking-[0.2em]">MODEVA</span>
                    </div>
                @endif
            </div>
        </div>

        <section class="flex flex-col py-1 sm:py-4">
            <p class="text-[10px] font-medium uppercase tracking-[0.28em] text-[#8A8178]">{{ $product->brand }}</p>
            <h1 class="mt-3 font-serif text-4xl leading-tight text-[#1C1917] sm:text-5xl">{{ $product->name }}</h1>
            <p class="mt-3 font-mono text-xs tracking-wide text-[#8A8178]">SKU {{ $product->sku }}</p>
            <p class="mt-5 text-2xl font-medium text-[#1C1917]">{{ \App\Support\Rupiah::format((int) $product->price) }}</p>

            <div class="mt-7 border-y border-[#E7E0D7] py-5">
                <h2 class="text-xs font-medium uppercase tracking-[0.16em] text-[#57534E]">Deskripsi</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-[#6B625A]">{{ $product->description }}</p>
            </div>

            @if ($product->variants->isNotEmpty())
                <div class="mt-6 space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="space-y-2">
                            <span class="block text-xs font-medium uppercase tracking-[0.14em] text-[#57534E]">Warna</span>
                            <select id="variant-color" class="w-full rounded-xl border border-[#D9D0C5] bg-white px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]" @disabled(! $hasAvailableStock)>
                                <option value="">Pilih warna</option>
                            </select>
                        </label>
                        <label class="space-y-2">
                            <span class="block text-xs font-medium uppercase tracking-[0.14em] text-[#57534E]">Ukuran</span>
                            <select id="variant-size" class="w-full rounded-xl border border-[#D9D0C5] bg-white px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C]" disabled>
                                <option value="">Pilih ukuran</option>
                            </select>
                        </label>
                    </div>
                    <div id="variant-stock-status" role="status" aria-live="polite" class="min-h-6 text-sm text-[#78716C]">
                        @if ($hasAvailableStock)
                            Pilih warna dan ukuran untuk melihat ketersediaan.
                        @else
                            <span class="font-medium text-rose-800">Habis</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="mt-6 rounded-xl bg-[#F4EFEB] px-4 py-3 text-sm text-[#78716C]">Varian produk belum tersedia.</div>
            @endif

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end">
                <label class="w-full space-y-2 sm:w-28">
                    <span class="block text-xs font-medium uppercase tracking-[0.14em] text-[#57534E]">Jumlah</span>
                    <input id="quantity" name="quantity" type="number" value="1" min="1" step="1" @disabled(! $hasAvailableStock) class="w-full rounded-xl border border-[#D9D0C5] bg-white px-4 py-3 text-sm focus:border-[#78716C] focus:outline-none focus:ring-1 focus:ring-[#78716C] disabled:bg-[#F4EFEB]">
                </label>
                <button id="add-to-cart" type="button" @disabled(! $hasAvailableStock) class="inline-flex min-h-12 flex-1 items-center justify-center rounded-full bg-[#1F1F1E] px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-[#33312E] disabled:cursor-not-allowed disabled:bg-[#A8A29E]">
                    Tambah ke Keranjang
                </button>
            </div>
            <p class="mt-3 text-xs leading-5 text-[#8A8178]">Keranjang belum tersedia. Pilihan produk belum dapat dikirim sebagai pesanan.</p>
            <p id="cart-notice" class="mt-2 min-h-5 text-xs font-medium text-[#78716C]" role="status" aria-live="polite"></p>
        </section>
    </div>

    @if ($relatedProducts->isNotEmpty())
        <section class="mt-16 border-t border-[#E7E0D7] pt-10 sm:mt-20 sm:pt-12">
            <div class="mb-6 flex items-end justify-between gap-4">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-[0.25em] text-[#8A8178]">Pilihan serupa</p>
                    <h2 class="mt-2 font-serif text-3xl text-[#1C1917] sm:text-4xl">Dari kategori yang sama</h2>
                </div>
                <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="shrink-0 text-xs font-medium text-[#57534E] underline decoration-[#D0C5B8] underline-offset-4">
                    Lihat semua
                </a>
            </div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-2 sm:gap-x-5 lg:grid-cols-4 lg:gap-x-7">
                @foreach ($relatedProducts as $relatedProduct)
                    @include('storefront.products.partials.card', ['product' => $relatedProduct])
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
    @if ($product->variants->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const variants = {{ \Illuminate\Support\Js::from($variantOptions) }};
                const colorSelect = document.getElementById('variant-color');
                const sizeSelect = document.getElementById('variant-size');
                const quantityInput = document.getElementById('quantity');
                const stockStatus = document.getElementById('variant-stock-status');
                const cartButton = document.getElementById('add-to-cart');
                const cartNotice = document.getElementById('cart-notice');

                const updateStock = () => {
                    const selectedVariant = variants.find((variant) =>
                        variant.color === colorSelect.value && variant.size === sizeSelect.value
                    );

                    if (!selectedVariant) {
                        stockStatus.textContent = 'Pilih warna dan ukuran untuk melihat ketersediaan.';
                        stockStatus.className = 'min-h-6 text-sm text-[#78716C]';
                        quantityInput.disabled = true;
                        cartButton.disabled = true;
                        quantityInput.removeAttribute('max');
                        return;
                    }

                    if (selectedVariant.stock === 0) {
                        stockStatus.textContent = 'Habis';
                        stockStatus.className = 'min-h-6 text-sm font-medium text-rose-800';
                        quantityInput.disabled = true;
                        cartButton.disabled = true;
                        quantityInput.removeAttribute('max');
                        return;
                    }

                    const isLowStock = selectedVariant.stock <= selectedVariant.low_stock_threshold;
                    stockStatus.textContent = isLowStock
                        ? `Stok menipis · Tersedia: ${selectedVariant.stock} pcs`
                        : `Tersedia: ${selectedVariant.stock} pcs`;
                    stockStatus.className = `min-h-6 text-sm ${isLowStock ? 'font-medium text-amber-800' : 'text-emerald-800'}`;
                    quantityInput.max = selectedVariant.stock;
                    quantityInput.disabled = false;
                    quantityInput.value = Math.min(Math.max(Number(quantityInput.value) || 1, 1), selectedVariant.stock);
                    cartButton.disabled = false;
                };

                const updateSizes = () => {
                    const colorVariants = variants.filter((variant) => variant.color === colorSelect.value);
                    const selectedSize = sizeSelect.value;
                    sizeSelect.replaceChildren(new Option('Pilih ukuran', ''));

                    [...new Set(colorVariants.map((variant) => variant.size))].forEach((size) => {
                        const variant = colorVariants.find((item) => item.size === size);
                        const option = new Option(size, size, false, false);
                        option.disabled = variant.stock === 0;
                        sizeSelect.add(option);
                    });

                    const nextAvailable = colorVariants.find((variant) => variant.stock > 0 && variant.size === selectedSize)
                        ?? colorVariants.find((variant) => variant.stock > 0);

                    if (nextAvailable) {
                        sizeSelect.value = nextAvailable.size;
                    }

                    sizeSelect.disabled = !colorVariants.some((variant) => variant.stock > 0);
                    updateStock();
                };

                [...new Set(variants.map((variant) => variant.color))].forEach((color) => {
                    const colorVariants = variants.filter((variant) => variant.color === color);
                    const option = new Option(color, color, false, false);
                    option.disabled = !colorVariants.some((variant) => variant.stock > 0);
                    colorSelect.add(option);
                });

                const firstAvailableColor = variants.find((variant) => variant.stock > 0)?.color;

                if (firstAvailableColor) {
                    colorSelect.value = firstAvailableColor;
                    updateSizes();
                }

                colorSelect.addEventListener('change', updateSizes);
                sizeSelect.addEventListener('change', updateStock);
                quantityInput.addEventListener('change', updateStock);

                cartButton.addEventListener('click', () => {
                    cartNotice.textContent = 'Fitur keranjang belum tersedia. Silakan kembali lagi nanti.';
                });
            });
        </script>
    @endif
@endpush
