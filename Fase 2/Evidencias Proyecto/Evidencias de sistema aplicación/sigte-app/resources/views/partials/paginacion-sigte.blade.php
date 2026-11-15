{{-- Paginación única para SIGTE (sin Bootstrap/Tailwind: una sola barra). --}}
@if ($paginator->hasPages())
    <nav class="sigte-paginacion" aria-label="Paginación">
        <p class="sigte-paginacion-info">
            Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>
        <ul class="sigte-paginacion-lista">
            @if ($paginator->onFirstPage())
                <li class="is-disabled" aria-disabled="true"><span aria-hidden="true">‹</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">‹</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="is-disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="is-activa" aria-current="page"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente">›</a></li>
            @else
                <li class="is-disabled" aria-disabled="true"><span aria-hidden="true">›</span></li>
            @endif
        </ul>
    </nav>
@endif
