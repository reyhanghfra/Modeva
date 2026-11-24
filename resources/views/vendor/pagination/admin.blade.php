@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4 px-2">
        {{-- Info pagination --}}
        <div class="text-xs text-[#78716C]">
            Menampilkan
            <span class="font-semibold text-[#1C1917]">{{ $paginator->firstItem() }}</span>
            sampai
            <span class="font-semibold text-[#1C1917]">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-[#1C1917]">{{ $paginator->total() }}</span>
            hasil
        </div>

        {{-- Page buttons --}}
        <div class="inline-flex items-center gap-1.5 select-none">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-[#A8A29E] bg-[#F5F2EB] rounded-full cursor-not-allowed border border-[#EFECE6]" aria-disabled="true">
                    <x-icon name="chevron-left" class="w-3.5 h-3.5 mr-1" strokeWidth="2" />
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-[#1C1917] bg-[#EDE7DF] hover:bg-[#E2DAD0] rounded-full transition-colors border border-[#E5DDD2]">
                    <x-icon name="chevron-left" class="w-3.5 h-3.5 mr-1" strokeWidth="2" />
                    Sebelumnya
                </a>
            @endif

            {{-- Elemen Nomor Halaman --}}
            <div class="hidden sm:inline-flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- Separator Titik Tiga "..." --}}
                    @if (is_string($element))
                        <span class="px-2.5 py-1 text-xs text-[#A8A29E]">{{ $element }}</span>
                    @endif

                    {{-- Array of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex items-center justify-center w-7 h-7 text-xs font-semibold text-white bg-[#222222] rounded-full shadow-xs" aria-current="page">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center w-7 h-7 text-xs font-medium text-[#1C1917] bg-[#EDE7DF] hover:bg-[#E2DAD0] rounded-full transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Tombol Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-[#1C1917] bg-[#EDE7DF] hover:bg-[#E2DAD0] rounded-full transition-colors border border-[#E5DDD2]">
                    Berikutnya
                    <x-icon name="chevron-right" class="w-3.5 h-3.5 ml-1" strokeWidth="2" />
                </a>
            @else
                <span class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-[#A8A29E] bg-[#F5F2EB] rounded-full cursor-not-allowed border border-[#EFECE6]" aria-disabled="true">
                    Berikutnya
                    <x-icon name="chevron-right" class="w-3.5 h-3.5 ml-1" strokeWidth="2" />
                </span>
            @endif
        </div>
    </nav>
@endif
