@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman">
        <div class="flex items-center justify-between gap-3 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-11 items-center rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] px-4 text-sm font-medium text-[#A2876A]">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-11 items-center rounded-2xl border border-[#E1D3C5] bg-white px-4 text-sm font-medium text-[#5C432C] transition hover:border-[#D4A017] hover:text-[#D4A017]">
                    Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-11 items-center rounded-2xl border border-[#D4A017] bg-[#D4A017] px-4 text-sm font-semibold text-white transition hover:bg-[#B56D3E]">
                    Berikutnya
                </a>
            @else
                <span class="inline-flex h-11 items-center rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] px-4 text-sm font-medium text-[#A2876A]">
                    Berikutnya
                </span>
            @endif
        </div>

        <div class="hidden sm:flex sm:items-center sm:justify-between sm:gap-6">
            <p class="text-sm leading-5 text-[#5C432C]">
                Menampilkan
                @if ($paginator->firstItem())
                    <span class="font-semibold">{{ $paginator->firstItem() }}</span>
                    sampai
                    <span class="font-semibold">{{ $paginator->lastItem() }}</span>
                @else
                    <span class="font-semibold">{{ $paginator->count() }}</span>
                @endif
                dari
                <span class="font-semibold">{{ $paginator->total() }}</span>
                data
            </p>

            <span class="inline-flex overflow-hidden rounded-2xl border border-[#E1D3C5] bg-white shadow-sm">
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Sebelumnya" class="inline-flex h-12 w-14 cursor-not-allowed items-center justify-center border-r border-[#E1D3C5] bg-[#FAF6F0] text-[#A2876A]">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" class="inline-flex h-12 w-14 items-center justify-center border-r border-[#E1D3C5] text-[#5C432C] transition hover:bg-[#FAF6F0] hover:text-[#D4A017]">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex h-12 min-w-12 items-center justify-center border-r border-[#E1D3C5] px-4 text-sm font-medium text-[#8B7359]">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex h-12 min-w-12 items-center justify-center border-r border-[#B56D3E] bg-gradient-to-r from-[#D4A017] to-[#B56D3E] px-4 text-sm font-semibold text-white">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="inline-flex h-12 min-w-12 items-center justify-center border-r border-[#E1D3C5] px-4 text-sm font-medium text-[#5C432C] transition hover:bg-[#FAF6F0] hover:text-[#D4A017]">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya" class="inline-flex h-12 w-14 items-center justify-center text-[#5C432C] transition hover:bg-[#FAF6F0] hover:text-[#D4A017]">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Berikutnya" class="inline-flex h-12 w-14 cursor-not-allowed items-center justify-center bg-[#FAF6F0] text-[#A2876A]">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
