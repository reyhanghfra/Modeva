@props([
    'title',
    'subtitle' => null,
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-[#1C1917] tracking-tight">
            {{ $title }}
        </h1>
        @if ($subtitle)
            <p class="text-sm text-[#78716C] mt-1 font-normal">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if (isset($action))
        <div class="shrink-0 flex items-center gap-3">
            {{ $action }}
        </div>
    @endif
</div>
