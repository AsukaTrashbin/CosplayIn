@if ($paginator->hasPages())
    <nav class="catalog-pagination d-flex justify-content-center align-items-center gap-3 mt-5" aria-label="Navigasi halaman katalog">
        @if (! $paginator->onFirstPage())
            <a href="{{ $paginator->previousPageUrl() }}" class="catalog-page-arrow" rel="prev">
                &larr; Sebelumnya
            </a>
        @endif

        <ul class="list-unstyled d-flex align-items-center gap-2 mb-0">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="px-2 text-muted-ci">{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span class="catalog-page-pill active" aria-current="page">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" class="catalog-page-pill">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="catalog-page-arrow" rel="next">
                Berikutnya &rarr;
            </a>
        @endif
    </nav>
@endif
