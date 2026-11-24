@props([
    'name' => 'search',
    'value' => request('search'),
    'placeholder' => 'Cari...',
    'formAction' => null,
])

<form method="GET" action="{{ $formAction ?? url()->current() }}" class="relative w-full max-w-xs sm:max-w-sm">
    {{-- Preserve other query params --}}
    @foreach (request()->except($name, 'page') as $param => $val)
        @if (is_array($val))
            @foreach ($val as $subVal)
                <input type="hidden" name="{{ $param }}[]" value="{{ $subVal }}">
            @endforeach
        @elseif (!is_null($val) && $val !== '')
            <input type="hidden" name="{{ $param }}" value="{{ $val }}">
        @endif
    @endforeach

    <div class="relative flex items-center">
        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8A827A]">
            <x-icon name="search" class="w-4 h-4" strokeWidth="2" />
        </span>
        <input
            type="text"
            name="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            class="block w-full pl-10 pr-9 py-2 sm:py-2.5 text-xs sm:text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-full border border-[#EFECE6] focus:border-[#222] focus:ring-1 focus:ring-[#222] focus:outline-none shadow-2xs transition-colors"
        >
        @if ($value)
            <a
                href="{{ request()->fullUrlWithQuery([$name => null, 'page' => null]) }}"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#A8A29E] hover:text-[#1C1917]"
                title="Hapus pencarian"
            >
                <x-icon name="x" class="w-4 h-4" strokeWidth="2" />
            </a>
        @endif
    </div>
</form>
