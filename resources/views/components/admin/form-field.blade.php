@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'helper' => null,
])

<div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-xs sm:text-sm font-medium text-[#1C1917]">
        {{ $label }}
        @if ($required)
            <span class="text-rose-600">*</span>
        @endif
    </label>

    @if (isset($slot) && $slot->isNotEmpty())
        {{ $slot }}
    @else
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if ($required) required @endif
            {{ $attributes->merge(['class' => 'block w-full px-4 py-2.5 text-sm bg-white text-[#1C1917] placeholder-[#A8A29E] rounded-xl border ' . ($errors->has($name) ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500' : 'border-[#EFECE6] focus:border-[#222] focus:ring-[#222]') . ' focus:outline-none focus:ring-1 transition-colors shadow-2xs']) }}
        >
    @endif

    @if ($helper)
        <p class="text-xs text-[#78716C]">{{ $helper }}</p>
    @endif

    @error($name)
        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
    @enderror
</div>
