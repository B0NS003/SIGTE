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
    $volverNombre = match ($claves[$caja['fase_idx']] ?? '') {
        'lavado' => 'Recepción',
        'preparacion' => 'Lavado',
        default => '',
    };
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
                    <span class="badge badge-urgent">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 4.5 20.5 19.5h-17L12 4.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            <path d="M12 10v4.2" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            <path d="M12 17.2h.01" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"/>
                        </svg>
                        Requiere atención
                    </span>
                @endif
                <span class="badge badge-listo" data-proceso-listo @unless (! empty($caja['proceso_listo'])) hidden @endunless>
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Listo
                </span>
            </div>
            <p>{{ $caja['servicio'] }} · {{ $caja['ubicacion'] }} · {{ $caja['operadora'] }}</p>
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

    <div
    class="proceso{{ ! empty($caja['proceso_listo']) ? ' is-listo' : '' }}"
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
                    @if ($volverNombre !== '')
                        <button class="btn btn-ghost btn-corregir" type="button" data-abrir="retroceso" data-caja="{{ $caja['id'] }}" data-volver="{{ $volverNombre }}">
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M14.5 6.5 9 12l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Corregir etapa
                        </button>
                    @endif
                    @if ($destinoNombre !== '')
                        <button class="btn btn-dark" type="button" data-abrir="etapa" data-caja="{{ $caja['id'] }}">
                            Pasar de etapa
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9.5 6.5 15 12l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
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
                    <dt>Actividad</dt>
                    <dd data-actividad>{{ $caja['actividad'] ?: 'Sin anotar' }}</dd>
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
