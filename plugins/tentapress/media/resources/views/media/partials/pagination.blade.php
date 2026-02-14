@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator $paginator */
    $currentPage = method_exists($paginator, 'currentPage') ? (int) $paginator->currentPage() : 1;
    $lastPage = method_exists($paginator, 'lastPage') ? (int) $paginator->lastPage() : 1;
    $startPage = max(1, $currentPage - 2);
    $endPage = min($lastPage, $currentPage + 2);
@endphp

@if ($lastPage > 1)
    <nav class="flex flex-wrap items-center justify-between gap-3" aria-label="Media pagination">
        <div class="text-xs text-black/60">
            Page {{ number_format($currentPage) }} of {{ number_format($lastPage) }}
        </div>

        <div class="flex flex-wrap items-center gap-1.5">
            @if (method_exists($paginator, 'onFirstPage') && $paginator->onFirstPage())
                <span class="tp-button-disabled min-w-22 justify-center border border-black/20 bg-black/15 text-black/45">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="tp-button-secondary min-w-22 justify-center">Previous</a>
            @endif

            @if ($startPage > 1)
                <a href="{{ $paginator->url(1) }}" class="tp-button-secondary min-w-10 justify-center px-2">{{ number_format(1) }}</a>
                @if ($startPage > 2)
                    <span class="px-1 text-xs font-semibold text-black/50" aria-hidden="true">…</span>
                @endif
            @endif

            @for ($page = $startPage; $page <= $endPage; $page++)
                @if ($page === $currentPage)
                    <span
                        class="tp-button-primary min-w-10 justify-center px-2 shadow-[inset_0_0_0_1px_rgba(255,255,255,0.35)]"
                        aria-current="page">
                        {{ number_format($page) }}
                    </span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="tp-button-secondary min-w-10 justify-center px-2">
                        {{ number_format($page) }}
                    </a>
                @endif
            @endfor

            @if ($endPage < $lastPage)
                @if ($endPage < $lastPage - 1)
                    <span class="px-1 text-xs font-semibold text-black/50" aria-hidden="true">…</span>
                @endif
                <a href="{{ $paginator->url($lastPage) }}" class="tp-button-secondary min-w-10 justify-center px-2">
                    {{ number_format($lastPage) }}
                </a>
            @endif

            @if (method_exists($paginator, 'hasMorePages') && $paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="tp-button-secondary min-w-22 justify-center">Next</a>
            @else
                <span class="tp-button-disabled min-w-22 justify-center border border-black/20 bg-black/15 text-black/45">Next</span>
            @endif
        </div>
    </nav>
@endif
