@php
    $salaDe = function (int $i): string {
        if ($i <= 1) {
            return 'lavado';
        }
        if ($i <= 3) {
            return 'armado';
        }

        return 'esteril';
    };
    $salaLabel = [
        'lavado' => 'Sala lavado',
        'armado' => 'Sala armado',
        'esteril' => 'Material estéril',
    ];
    $sala = $salaDe($caja['fase_idx']);
    $etapaActual = $fases[$caja['fase_idx']];
@endphp
<article class="track-card {{ $caja['urgente'] ? 'is-urgent' : '' }}">
    <header class="track-head">
        <div>
            <div class="track-title">
                <h2>{{ $caja['id'] }}</h2>
                @if ($caja['urgente'])
                    <span class="badge badge-urgent">Requiere atención</span>
                @endif
            </div>
            <p>{{ $caja['servicio'] }} · {{ $caja['ubicacion'] }} · {{ $caja['tiempo'] }} · {{ $caja['operadora'] }}</p>
        </div>
        <div class="track-head-badges">
            <span class="badge badge-sala badge-{{ $sala }}">{{ $salaLabel[$sala] }}</span>
            <span class="badge">{{ $etapaActual }}</span>
        </div>
    </header>

    <div class="track-pipe" aria-label="Etapa actual: {{ $etapaActual }}">
            @foreach ($fases as $i => $fase)
                @php
                    $done = $i < $caja['fase_idx'];
                    $current = $i === $caja['fase_idx'];
                @endphp
                <div class="track-step {{ $done ? 'done' : '' }} {{ $current ? 'current' : '' }}">
                    <div class="track-node"></div>
                    <div class="track-label">{{ $fase }}</div>
                </div>
                @if (! $loop->last)
                    <div class="track-line {{ $i < $caja['fase_idx'] ? 'done' : '' }}"></div>
                @endif
            @endforeach
    </div>

    <details class="track-more">
        <summary>
            <span class="track-summary-label">Más información</span>
            <a class="btn btn-dark" href="{{ route('mockups.avanzar', ['caja' => $caja['id']]) }}" onclick="event.stopPropagation()">Aceptar avance</a>
        </summary>

            <dl class="track-facts">
                <div>
                    <dt>Etapa actual</dt>
                    <dd>{{ $etapaActual }}</dd>
                </div>
                <div>
                    <dt>Ubicación</dt>
                    <dd>{{ $caja['ubicacion'] }}</dd>
                </div>
                <div>
                    <dt>Tiempo en la etapa</dt>
                    <dd>{{ $caja['tiempo'] }} <small>desde {{ $caja['hora'] }}</small></dd>
                </div>
                <div>
                    <dt>Responsable</dt>
                    <dd>{{ $caja['operadora'] }}</dd>
                </div>
            </dl>

            <footer class="track-foot">
                <span>{{ $caja['estado'] }}</span>
                <div class="track-actions">
                    <button class="btn btn-ghost" type="button" disabled title="Detalle después">Modificar</button>
                </div>
            </footer>
    </details>
</article>
