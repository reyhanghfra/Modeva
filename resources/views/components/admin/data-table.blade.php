@props([
    'headers' => [],
    'paginator' => null,
])

<div class="bg-white rounded-3xl border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-[#EFECE6] bg-[#FAF8F5]/60">
                    @foreach ($headers as $header)
                        <th scope="col" class="px-5 sm:px-6 py-3.5 text-[11px] font-semibold tracking-wider text-[#78716C] uppercase select-none {{ is_array($header) ? ($header['class'] ?? '') : '' }}">
                            {{ is_array($header) ? ($header['label'] ?? '') : $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F3EFEA] text-sm text-[#1C1917]">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if ($paginator && $paginator->hasPages())
        <div class="p-4 sm:px-6 border-t border-[#EFECE6]">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
