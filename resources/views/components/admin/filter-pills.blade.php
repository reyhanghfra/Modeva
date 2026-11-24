@props([
    'name',
    'options' => [], // ['' => 'Semua', 'admin' => 'Admin', ...]
    'selected' => request($name, ''),
])

<div class="inline-flex flex-wrap items-center gap-1.5">
    @foreach ($options as $key => $label)
        @php
            $isActive = (string) $selected === (string) $key;
            $url = request()->fullUrlWithQuery([$name => $key !== '' ? $key : null, 'page' => null]);
        @endphp
        <a
            href="{{ $url }}"
            class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-medium transition-all select-none {{ $isActive ? 'bg-[#222222] text-white shadow-2xs' : 'bg-[#EDE7DF] text-[#78716C] hover:text-[#1C1917] hover:bg-[#E5DDD2]' }}"
        >
            {{ $label }}
        </a>
    @endforeach
</div>
