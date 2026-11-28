@props([
    'label',
    'value',
    'icon' => null,
    'subtext' => null,
    'subtextClass' => 'text-emerald-700',
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col justify-between transition-all ' . ($href ? 'hover:shadow-[0_4px_20px_rgba(0,0,0,0.06)] hover:border-[#D0C5B8] cursor-pointer group' : 'hover:shadow-[0_4px_20px_rgba(0,0,0,0.04)]')]) }}
>
    <div class="flex items-start justify-between gap-3">
        <span class="text-xs sm:text-sm font-medium text-[#78716C] tracking-wide {{ $href ? 'group-hover:text-[#1C1917]' : '' }} transition-colors">
            {{ $label }}
        </span>

        @if ($icon)
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-[#EDE7DF] flex items-center justify-center text-[#222222] shrink-0 {{ $href ? 'group-hover:scale-105' : '' }} transition-transform">
                <x-icon :name="$icon" class="w-5 h-5 text-[#222222]" strokeWidth="1.75" />
            </div>
        @endif
    </div>

    <div class="mt-4 sm:mt-5">
        <div class="font-serif text-3xl sm:text-4xl text-[#1C1917] font-semibold tracking-tight truncate leading-tight" title="{{ $value }}">
            {{ $value }}
        </div>

        @if ($subtext)
            <p class="text-xs sm:text-[13px] mt-2 font-medium {{ $subtextClass }} flex items-center gap-1">
                {{ $subtext }}
            </p>
        @else
            <div class="h-4 sm:h-[18px] mt-2"></div>
        @endif
    </div>
</{{ $tag }}>
