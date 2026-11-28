@props(['product', 'href' => '#', 'new' => false])

@php
    $img = $product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)
        ? \Illuminate\Support\Facades\Storage::url($product->image)
        : null;
    $stock = $product->total_stock;
    [$variant, $label] = match (true) {
        $stock <= 0 => ['danger', 'Habis'],
        $stock <= 10 => ['warning', 'Sisa ' . $stock],
        default => ['success', 'Tersedia'],
    };
@endphp

<a href="{{ $href }}" class="group block">
    <div class="relative aspect-[4/5] rounded-3xl overflow-hidden bg-[#EDE7DF]">
        @if ($img)
            <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @endif
        @if ($new)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-white text-xs font-medium text-[#1C1917]">Baru</span>
        @endif
        <x-status-badge :variant="$variant" :label="$label" class="absolute bottom-3 left-3" />
    </div>
    <div class="mt-4 px-1">
        <p class="text-xs uppercase tracking-[0.2em] text-[#78716C]">{{ $product->brand }}</p>
        <h3 class="mt-1 text-[#1C1917]">{{ $product->name }}</h3>
        <p class="mt-1 font-semibold text-[#1C1917]">{{ \App\Support\Rupiah::format((int) $product->price) }}</p>
    </div>
</a>