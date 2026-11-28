@props([
    'label',
    'value',
    'icon' => null,
    'subtext' => null,
    'subtextClass' => 'text-emerald-700',
    'href' => null,
])

<x-stat-card
    {{ $attributes }}
    :label="$label"
    :value="$value"
    :icon="$icon"
    :subtext="$subtext"
    :subtext-class="$subtextClass"
    :href="$href"
/>
