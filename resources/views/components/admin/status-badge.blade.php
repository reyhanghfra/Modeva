@props([
    'variant' => 'warning',
    'label' => null,
])

@php
    $variantClasses = match ($variant) {
        'success' => 'bg-emerald-50 text-emerald-800 border-emerald-200/60',
        'danger' => 'bg-rose-50 text-rose-800 border-rose-200/60',
        default => 'bg-[#FAF2E8] text-[#8C5E28] border-[#ECD9C3]',
    };

    $dotClasses = match ($variant) {
        'success' => 'bg-emerald-500',
        'danger' => 'bg-rose-500',
        default => 'bg-[#B87A33]',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border {$variantClasses} tracking-tight select-none"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $dotClasses }} shrink-0"></span>
    <span>{{ $label ?? $slot }}</span>
</span>
