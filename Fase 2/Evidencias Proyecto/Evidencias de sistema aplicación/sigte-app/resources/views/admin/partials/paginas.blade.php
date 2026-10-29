@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $paginador */
    $actual = $paginador->currentPage();
    $ultima = $paginador->lastPage();
    $alrededor = 2;
    $numeros = [];
    if ($ultima <= 9) {
        $numeros = range(1, $ultima);
    } else {
        $numeros[] = 1;
        $desde = max(2, $actual - $alrededor);
        $hasta = min($ultima - 1, $actual + $alrededor);
        if ($desde > 2) {
            $numeros[] = null;
        }
        for ($n = $desde; $n <= $hasta; $n++) {
            $numeros[] = $n;
        }
        if ($hasta < $ultima - 1) {
            $numeros[] = null;
        }
        $numeros[] = $ultima;
    }
@endphp
@if ($paginador->total() > 0)
    <div class="aud-pager">
        <p class="aud-pager-meta">
            Total {{ number_format($paginador->total(), 0, ',', '.') }} resultados
            · Pág. {{ $actual }}/{{ max(1, $ultima) }}
            · {{ $paginador->firstItem() }}–{{ $paginador->lastItem() }}
        </p>
        @if ($ultima > 1)
            <nav class="aud-pager-nav" aria-label="{{ $etiqueta }}">
                @if ($actual > 1)
                    <a class="aud-pager-btn" href="{{ $paginador->url(1) }}" title="Primera" aria-label="Primera página">«</a>
                    <a class="aud-pager-btn" href="{{ $paginador->previousPageUrl() }}" title="Anterior" aria-label="Página anterior">‹</a>
                @else
                    <span class="aud-pager-btn is-off" aria-hidden="true">«</span>
                    <span class="aud-pager-btn is-off" aria-hidden="true">‹</span>
                @endif

                @foreach ($numeros as $numero)
                    @if ($numero === null)
                        <span class="aud-pager-btn is-gap" aria-hidden="true">…</span>
                    @elseif ($numero === $actual)
                        <span class="aud-pager-btn is-on" aria-current="page">{{ $numero }}</span>
                    @else
                        <a class="aud-pager-btn" href="{{ $paginador->url($numero) }}">{{ $numero }}</a>
                    @endif
                @endforeach

                @if ($paginador->hasMorePages())
                    <a class="aud-pager-btn" href="{{ $paginador->nextPageUrl() }}" title="Siguiente" aria-label="Página siguiente">›</a>
                    <a class="aud-pager-btn" href="{{ $paginador->url($ultima) }}" title="Última" aria-label="Última página">»</a>
                @else
                    <span class="aud-pager-btn is-off" aria-hidden="true">›</span>
                    <span class="aud-pager-btn is-off" aria-hidden="true">»</span>
                @endif
            </nav>
        @endif
    </div>
@endif
