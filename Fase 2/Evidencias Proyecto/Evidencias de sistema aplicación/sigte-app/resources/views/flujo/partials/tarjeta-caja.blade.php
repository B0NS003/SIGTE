@php
    $salaDe = function (int $i): ?string {
        if ($i < 1) {
            return null;
        }
        if ($i === 1) {
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
    $espera = trim((string) ($caja['tiempo'] ?? ''));
    $nombreCorto = function (?string $nombre): string {
        $partes = preg_split('/\s+/u', trim((string) $nombre), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($partes === []) {
            return 'Sin responsable';
        }
        if (count($partes) === 1) {
            return $partes[0];
        }
        if (count($partes) === 2) {
            return $partes[0].' '.$partes[1];
        }
        $apellido2 = array_pop($partes);
        $apellido1 = array_pop($partes);
        $inicial = mb_strtoupper(mb_substr($apellido2, 0, 1));

        return $partes[0].' '.$apellido1.' '.$inicial.'.';
    };
    $responsableCorto = $nombreCorto($caja['operadora'] ?? null);
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
        <div class="track-head-main">
            <div class="track-title">
                <h2>{{ $caja['id'] }}</h2>
                @if ($caja['urgente'])
                    <span class="badge badge-urgent">
                        <svg class="sigte-icon badge-urgent-bell" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
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
            <p class="track-meta">
                <span>{{ $caja['servicio'] }}</span>
                @if ($sala !== null)
                    <span class="track-meta-sep" aria-hidden="true">·</span>
                    <span class="track-meta-sala">{{ $salaLabel[$sala] }}</span>
                @endif
                @if ($espera !== '')
                    <span class="track-meta-sep" aria-hidden="true">·</span>
                    <span class="track-meta-tiempo">{{ $espera }}</span>
                @endif
                <span class="track-meta-sep" aria-hidden="true">·</span>
                <span class="track-meta-persona" title="{{ $caja['operadora'] }}">{{ $responsableCorto }}</span>
            </p>
        </div>
    </header>

    <div class="track-pipe" aria-label="Etapa actual: {{ $etapaActual }}">
        @foreach ($fases as $i => $fase)
            @php
                $done = $i < $caja['fase_idx'];
                $current = $i === $caja['fase_idx'];
            @endphp
            <div class="track-step {{ $done ? 'done' : '' }} {{ $current ? 'current' : '' }}" title="{{ $fase }}">
                <div class="track-node" aria-hidden="true">
                    @switch($i)
                        @case(0)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/>
                                <path d="M12 22V12"/>
                                <path d="m3.3 7 8.7 5 8.7-5"/>
                                <path d="m7.5 4.27 9 5.15"/>
                            </svg>
                            @break
                        @case(1)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3c-2.5 4-7 8.5-7 12a7 7 0 0 0 14 0c0-3.5-4.5-8-7-12z"/>
                                <path d="M9 16a3 3 0 0 0 3 3"/>
                            </svg>
                            @break
                        @case(2)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a2.121 2.121 0 0 1-3-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"/>
                            </svg>
                            @break
                        @case(3)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M9 12l2 2 4-4"/>
                                <path d="M12 3v2m0 14v2M3 12h2m14 0h2"/>
                            </svg>
                            @break
                        @case(4)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="5" x="2" y="3" rx="1"/>
                                <path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/>
                                <path d="M10 12h4"/>
                            </svg>
                            @break
                        @case(5)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5"/>
                            </svg>
                            @break
                    @endswitch
                </div>
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

    <div class="track-foot-bar">
        <details class="track-more">
            <summary>
                <span class="track-summary-label">Más información</span>
            </summary>
            <div class="track-detalle">
                <div class="track-dato">
                    <span>Actividad</span>
                    <strong data-actividad>{{ $caja['actividad'] ?: 'Sin anotar' }}</strong>
                </div>
                <div class="track-dato">
                    <span>Ubicación</span>
                    <strong>{{ $caja['ubicacion'] }}</strong>
                </div>
                <div class="track-dato">
                    <span>Desde</span>
                    <strong>{{ $caja['hora'] }}</strong>
                </div>
                <div class="track-dato track-dato-wide">
                    <span>Responsable</span>
                    <strong>{{ $caja['operadora'] }}</strong>
                </div>
            </div>
        </details>
        @if (($usuario['rol'] ?? '') === 'Operadora')
            <div class="track-card-actions">
                @if ($volverNombre !== '')
                    <button class="btn btn-ghost btn-corregir" type="button" data-abrir="retroceso" data-caja="{{ $caja['id'] }}" data-volver="{{ $volverNombre }}">
                        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M14.5 6.5 9 12l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Corregir
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
            </div>
        @endif
    </div>
</article>
@include('flujo.partials.track-anim')
