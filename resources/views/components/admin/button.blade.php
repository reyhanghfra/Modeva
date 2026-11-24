@props([
    'variant' => 'primary', // primary | secondary | danger | outline
    'type' => 'button',
    'href' => null,
    'size' => 'md',
    'icon' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-full transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 select-none cursor-pointer';

    $sizeClasses = match ($size) {
        'sm' => 'text-xs px-3.5 py-1.5 gap-1.5',
        'lg' => 'text-base px-6 py-3 gap-2.5',
        default => 'text-xs sm:text-sm px-4 sm:px-5 py-2 sm:py-2.5 gap-2',
    };

    $variantClasses = match ($variant) {
        'secondary' => 'bg-[#EDE7DF] hover:bg-[#E5DDD2] text-[#1C1917] border border-[#E5DDD2] focus:ring-[#D0C5B8]',
        'danger' => 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/80 focus:ring-rose-500',
        'danger-solid' => 'bg-rose-600 hover:bg-rose-700 text-white focus:ring-rose-500 shadow-2xs',
        'outline' => 'bg-transparent hover:bg-[#EDE7DF]/60 text-[#1C1917] border border-[#D0C5B8] focus:ring-[#D0C5B8]',
        default => 'bg-[#222222] hover:bg-[#33312E] text-white shadow-2xs focus:ring-[#222222]', // primary pill
    };

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon :name="$icon" class="w-4 h-4 shrink-0" strokeWidth="2" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon :name="$icon" class="w-4 h-4 shrink-0" strokeWidth="2" />
        @endif
        {{ $slot }}
    </button>
@endif
