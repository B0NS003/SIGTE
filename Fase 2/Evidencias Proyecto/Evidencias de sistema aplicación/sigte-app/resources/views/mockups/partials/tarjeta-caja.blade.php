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
    $claves = \App\Models\Caja::etapas();
    $destino = $claves[$caja['fase_idx'] + 1] ?? '';
    $destinoNombre = $fases[$caja['fase_idx'] + 1] ?? '';
@endphp
<article
    class="track-card {{ $caja['urgente'] ? 'is-urgent' : '' }} {{ ! empty($caja['proceso_listo']) ? 'is-proceso-listo' : '' }}"
    data-caja="{{ $caja['id'] }}"
    data-servicio="{{ $caja['servicio'] }}"
    data-ubicacion="{{ $caja['ubicacion'] }}"
    data-tiempo="{{ $caja['tiempo'] }}"
    data-fase="{{ $etapaActual }}"
    data-etapa="{{ $claves[$caja['fase_idx']] }}"
    data-destino="{{ $destino }}"
    data-destino-nombre="{{ $destinoNombre }}"
    data-fecha="{{ $caja['fecha'] }}"
    data-hora="{{ $caja['hora'] }}"
    data-operadora="{{ $caja['operadora'] }}"
    data-minutos="{{ $caja['minutos'] }}"
>
    <header class="track-head">
        <div>
            <div class="track-title">
                <h2>{{ $caja['id'] }}</h2>
                @if ($caja['urgente'])
                    <span class="badge badge-urgent">Requiere atención</span>
                @endif
            </div>
            <p>{{ $caja['servicio'] }} · {{ $caja['ubicacion'] }}@if (empty($caja['proceso_hasta']))<span class="track-lleva" data-lleva="{{ $caja['etapa_iso'] }}"> · {{ $caja['tiempo'] }}</span>@endif · {{ $caja['operadora'] }}</p>
        </div>
        <div class="track-head-badges">
            <span class="badge badge-sala badge-{{ $sala }}">{{ $salaLabel[$sala] }}</span>
            <span class="badge">{{ $etapaActual }}</span>
            <span class="badge badge-listo" data-proceso-listo @unless (! empty($caja['proceso_listo'])) hidden @endunless>Listo</span>
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

    <div
        class="proceso"
        data-desde="{{ $caja['proceso_desde'] }}"
        data-hasta="{{ $caja['proceso_hasta'] }}"
        @unless ($caja['proceso_hasta']) hidden @endunless
    >
        <div class="proceso-pista" aria-hidden="true"><span class="proceso-barra"></span></div>
        <p class="proceso-texto">{{ ! empty($caja['proceso_listo']) ? 'Listo para pasar' : 'En curso' }}</p>
    </div>

    <details class="track-more">
        <summary>
            <span class="track-summary-label">Más información</span>
            @if (($usuario['rol'] ?? '') === 'Operadora')
                <span class="track-card-actions" onclick="event.stopPropagation()">
                    @if ($destinoNombre !== '')
                        <button class="btn btn-dark" type="button" data-abrir="etapa" data-caja="{{ $caja['id'] }}">Pasar de etapa</button>
                    @else
                        <a class="btn btn-dark" href="{{ route('mockups.entrega', ['caja' => $caja['id']]) }}">Registrar entrega</a>
                    @endif
                </span>
            @endif
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
                    <dt>Desde</dt>
                    <dd>{{ $caja['hora'] }}</dd>
                </div>
                <div>
                    <dt>Responsable</dt>
                    <dd>{{ $caja['operadora'] }}</dd>
                </div>
            </dl>
    </details>
</article>
@include('mockups.partials.track-anim')
