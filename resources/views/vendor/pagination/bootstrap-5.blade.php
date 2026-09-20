@if ($paginator->hasPages())
    <nav class="dg-pagination-nav-wrapper d-flex justify-content-center align-items-center mt-2 mb-0 py-1" aria-label="Page navigation">
        <ul class="pagination dg-squircle-pagination d-flex align-items-center gap-2 mb-0 list-unstyled">
            
            {{-- First Page Link («) --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="First Page">
                    <span class="page-link dg-page-btn arrow-btn" aria-hidden="true">
                        <i class="bi bi-chevron-double-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link dg-page-btn arrow-btn" href="{{ $paginator->url(1) }}" rel="first" aria-label="First Page">
                        <i class="bi bi-chevron-double-left"></i>
                    </a>
                </li>
            @endif

            {{-- Previous Page Link (‹) --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link dg-page-btn arrow-btn" aria-hidden="true">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link dg-page-btn arrow-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link dg-page-btn dots-btn">&hellip;</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link dg-page-btn num-btn">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link dg-page-btn num-btn" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link (›) --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link dg-page-btn arrow-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link dg-page-btn arrow-btn" aria-hidden="true">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            @endif

            {{-- Last Page Link (») --}}
            @if ($paginator->currentPage() >= $paginator->lastPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Last Page">
                    <span class="page-link dg-page-btn arrow-btn" aria-hidden="true">
                        <i class="bi bi-chevron-double-right"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link dg-page-btn arrow-btn" href="{{ $paginator->url($paginator->lastPage()) }}" rel="last" aria-label="Last Page">
                        <i class="bi bi-chevron-double-right"></i>
                    </a>
                </li>
            @endif

        </ul>
    </nav>
@endif
