@props([
    'title',
    'icon' => null,
    'iconClass' => 'text-[#8C5E28]',
    'headerRight' => null,
])

<x-panel
    {{ $attributes }}
    :title="$title"
    :icon="$icon"
    :icon-class="$iconClass"
    :header-right="$headerRight"
>
    {{ $slot }}
</x-panel>
