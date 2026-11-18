@if ($paginator->hasPages())
    <nav aria-label="Navigasi halaman">
        <ul>
            @if ($paginator->onFirstPage())
                <li>Sebelumnya</li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><strong>Halaman {{ $page }}</strong></li>
                        @else
                            <li><a href="{{ $url }}">Halaman {{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a></li>
            @else
                <li>Berikutnya</li>
            @endif
        </ul>
        <p>Menampilkan {{ $paginator->firstItem() }} sampai {{ $paginator->lastItem() }} dari {{ $paginator->total() }} data.</p>
    </nav>
@endif
