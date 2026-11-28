@props([
    'title',
    'icon' => null,
    'iconClass' => 'text-[#8C5E28]',
    'headerRight' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] flex flex-col justify-between']) }}>
    <div class="flex items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2.5 min-w-0">
            @if ($icon)
                <x-icon :name="$icon" class="w-5 h-5 shrink-0 {{ $iconClass }}" strokeWidth="1.75" />
            @endif
            <h2 class="font-serif text-xl sm:text-2xl font-semibold text-[#1C1917] tracking-tight truncate">
                {{ $title }}
            </h2>
        </div>

        @if ($headerRight)
            <div class="shrink-0">
                {{ $headerRight }}
            </div>
        @endif
    </div>

    <div class="flex-1 flex flex-col">
        {{ $slot }}
    </div>
</div>
