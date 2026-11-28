@props([
    'variant' => 'warning',
    'label' => null,
])

<x-status-badge
    {{ $attributes }}
    :variant="$variant"
    :label="$label"
>
    {{ $slot }}
</x-status-badge>
