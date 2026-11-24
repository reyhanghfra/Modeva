@props([
    'src' => null,
    'alt' => 'Produk',
    'size' => 'w-10 h-10',
])

@if ($src)
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        {{ $attributes->merge(['class' => "{$size} rounded-xl object-cover shrink-0 border border-[#EFECE6]"]) }}
    >
@else
    <div {{ $attributes->merge(['class' => "{$size} rounded-xl bg-[#EDE7DF] flex items-center justify-center shrink-0 text-[#8A827A] border border-[#E5DDD2]"]) }}>
        <x-icon name="box" class="w-5 h-5 text-[#8A827A]" strokeWidth="1.5" />
    </div>
@endif
