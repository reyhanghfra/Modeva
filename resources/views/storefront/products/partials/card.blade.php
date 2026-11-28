@php
    $availableVariants = $product->variants->where('stock', '>', 0);
    $availableColors = $availableVariants->pluck('color')->unique();
@endphp

<article class="group min-w-0">
    <a href="{{ route('products.show', ['product' => $product->slug]) }}" class="block overflow-hidden rounded-2xl bg-[#EDE7DF]">
        <div class="relative aspect-[4/5] overflow-hidden">
            @if ($product->image)
                <img
                    src="{{ asset('storage/'.$product->image) }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                    onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')"
                >
                <div role="img" aria-label="Foto produk belum tersedia" class="hidden h-full w-full items-center justify-center bg-gradient-to-br from-[#F2EEE8] to-[#DED5C9] font-serif tracking-[0.18em] text-[#A89B8A]">
                    MODEVA
                </div>
            @else
                <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-[#F2EEE8] to-[#DED5C9] text-[#A89B8A]">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5 8.5 12l3 3 2-2 6.5 6.5M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm4 5h.01"/>
                    </svg>
                    <span class="mt-3 font-serif text-xl tracking-[0.18em]">MODEVA</span>
                </div>
            @endif
            @if ($product->total_stock === 0)
                <span class="absolute left-3 top-3 rounded-full bg-[#1F1F1E]/90 px-3 py-1.5 text-[10px] font-medium uppercase tracking-[0.12em] text-white">
                    Habis
                </span>
            @endif
        </div>
    </a>

    <div class="space-y-1.5 px-0.5 pt-3">
        <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[#8A8178]">{{ $product->brand }}</p>
        <a href="{{ route('products.show', ['product' => $product->slug]) }}" class="block font-serif text-xl leading-tight text-[#1C1917] transition-colors hover:text-[#78716C]">
            {{ $product->name }}
        </a>
        <p class="text-sm font-medium text-[#1C1917]">{{ \App\Support\Rupiah::format((int) $product->price) }}</p>
        <p class="pt-0.5 text-xs text-[#8A8178]">
            @if ($availableVariants->isNotEmpty())
                {{ $availableColors->count() }} warna <span aria-hidden="true">·</span> {{ $availableVariants->count() }} varian tersedia
            @else
                Stok belum tersedia
            @endif
        </p>
    </div>
</article>
