@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/enfermera.css') }}?v={{ @filemtime(public_path('css/enfermera.css')) }}">
@endpush

@section('title', 'SIGTE — Enfermera de turno')

@section('content')
<div class="shell">
    {{-- Menú original: sin cambios en las opciones ni en la barra lateral. --}}
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-sigte.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Turno</div>
            </div>
        </div>
                @include('partials.menu-rol')
        @include('partials.sidebar-foot')
    </aside>

    <main class="main enf-resumen">
        <div class="main-top enf-resumen-head">
            <div>
                <p class="eyebrow">Enfermera de turno</p>
                <h1>Supervisión del turno</h1>
                <p class="main-sub">Monitorea el procesamiento, revisa incidencias y prepara el traspaso del equipo.</p>
            </div>
            <div class="main-actions enf-resumen-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Ir al flujo</a>
                <a class="btn btn-dark" href="{{ route('mockups.cierre_turno') }}">{{ ($resumen_turno['ya_cerrado'] ?? false) ? 'Ver cierre' : 'Cerrar turno' }}</a>
            </div>
        </div>

        @if (!isset($resumen_turno) || !is_array($resumen_turno))
            <section class="panel enf-sin-datos" role="alert">
                <h2>Falta conectar los datos del resumen</h2>
                <p>El panel ya está instalado, pero falta agregar <code>resumen_turno</code> en el controlador, según las instrucciones de instalación.</p>
            </section>
        @else
            @php $d = $resumen_turno; @endphp

            {{-- Información institucional y de la guardia (misma fuente que Cierre de turno). --}}
            <section class="panel enf-turno-meta" aria-label="Turno actual">
                <div class="enf-meta-persona">
                    <span class="enf-icono enf-icono-naranja" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>
                    </span>
                    <div><span class="enf-sobretitulo">A cargo ahora</span><strong>{{ $d['turno']['responsable'] }}</strong><small>Responsable de la supervisión</small></div>
                </div>
                <div class="enf-meta-campo">
                    <span class="enf-icono enf-icono-plano" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18"/></svg>
                    </span>
                    <div><span class="enf-sobretitulo">Turno</span><strong>{{ $d['turno']['fecha'] }}</strong><small>{{ $d['turno']['bloque'] }}</small></div>
                </div>
                <div class="enf-meta-campo enf-meta-equipo">
                    <span class="enf-icono enf-icono-plano" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M17 14a5 5 0 0 1 4 5v1"/></svg>
                    </span>
                    <div><span class="enf-sobretitulo">Personal del bloque</span><strong class="enf-nombre-equipo">{{ $d['turno']['operadoras'] }}</strong><small>Según las asignaciones vigentes</small></div>
                </div>
                <div class="enf-turno-estado">
                    @if ($d['ya_cerrado'])
                        <span class="enf-estado enf-estado-cerrado"><span></span>Turno cerrado</span>
                    @else
                        <span class="enf-estado enf-estado-activo"><span></span>Turno sin cerrar</span>
                    @endif
                </div>
            </section>

            {{-- Indicadores con cifras reales: sin porcentajes de ejemplo. --}}
            <section class="enf-indicadores" aria-label="Indicadores del turno">
                <article class="panel enf-kpi enf-kpi-alerta">
                    <span class="enf-kpi-icono" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5M12 18h.01"/></svg></span>
                    <div><strong>{{ $d['alertas_total'] }}</strong><span>Alertas detectadas</span><small>Cajas urgentes y stock</small></div>
                    <a class="enf-kpi-link" href="{{ route('mockups.alertas') }}">Ver alertas</a>
                </article>
                <article class="panel enf-kpi enf-kpi-critico">
                    <span class="enf-kpi-icono" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5 9-5ZM3 8v9l9 5 9-5V8M12 13v9"/></svg></span>
                    <div><strong>{{ $d['criticas_total'] }}</strong><span>Cajas por revisar</span><small>Urgentes o con demora</small></div>
                    <a class="enf-kpi-link" href="{{ route('mockups.panel', 'operador') }}">Ver flujo</a>
                </article>
                <article class="panel enf-kpi enf-kpi-flujo">
                    <span class="enf-kpi-icono" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="18" rx="2"/><path d="M9 4V2h6v2M9 11h6M9 15h6"/></svg></span>
                    <div><strong>{{ $d['total_cajas'] }}</strong><span>Cajas en el flujo</span><small>Estado actual del sistema</small></div>
                </article>
                <article class="panel enf-kpi enf-kpi-entregas">
                    <span class="enf-kpi-icono" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 8h13v10H3zM16 11h3l3 4v3h-6z"/><circle cx="7" cy="18" r="2"/><circle cx="19" cy="18" r="2"/></svg></span>
                    <div><strong>{{ $d['entregas_turno'] }}</strong><span>Entregas del turno</span><small>Operaciones registradas</small></div>
                    <a class="enf-kpi-link" href="{{ route('mockups.historial_entregas') }}">Ver historial</a>
                </article>
            </section>

            <div class="enf-dos-columnas enf-zona-central">
                <section class="panel enf-bloque enf-atencion" aria-labelledby="enf-atencion-titulo">
                    <div class="enf-bloque-cabeza">
                        <div><div class="enf-titulo-con-icono"><span class="enf-mini-icono">!</span><h2 id="enf-atencion-titulo">Atención del turno</h2></div>
                            <p>Incidencias y cajas que requieren seguimiento.</p></div>
                        <a href="{{ route('mockups.alertas') }}" class="enf-enlace">Ver alertas <span aria-hidden="true">›</span></a>
                    </div>
                    <div class="enf-atencion-lista">
                        @forelse ($d['atencion'] as $item)
                            @php $esAlta = in_array($item['nivel'], ['Urgente', 'Crítico'], true); @endphp
                            <article class="enf-atencion-item">
                                <span class="enf-atencion-punto {{ $esAlta ? 'enf-punto-alto' : '' }}" aria-hidden="true"></span>
                                <div><strong>{{ $item['codigo'] }}</strong><p>{{ $item['detalle'] }}</p></div>
                                <span class="enf-pill {{ $esAlta ? 'enf-pill-alta' : 'enf-pill-media' }}">{{ $item['nivel'] }}</span>
                            </article>
                        @empty
                            <div class="enf-vacio"><strong>Sin incidencias por mostrar</strong><span>No se detectaron alertas ni demoras en las cajas activas.</span></div>
                        @endforelse
                    </div>
                    @if ($d['atencion_total'] > count($d['atencion']))
                        <p class="enf-bloque-pie">Mostrando {{ count($d['atencion']) }} de {{ $d['atencion_total'] }} elementos. Para revisar todas las alertas, abre la sección Alertas.</p>
                    @endif
                </section>

                <section class="panel enf-bloque enf-flujo" aria-labelledby="enf-flujo-titulo">
                    <div class="enf-bloque-cabeza">
                        <div><div class="enf-titulo-con-icono"><span class="enf-mini-icono enf-mini-capas" aria-hidden="true">▤</span><h2 id="enf-flujo-titulo">Carga por fase</h2></div>
                            <p>Distribución actual de las cajas entre las etapas del procesamiento.</p></div>
                        <span class="enf-resumen-total">{{ $d['total_cajas'] }} cajas</span>
                    </div>
                    <div class="enf-fases">
                        @foreach ($d['flujo'] as $i => $fase)
                            <article class="enf-fase">
                                <span class="enf-fase-icono" aria-hidden="true">
                                    @switch($i)
                                        @case(0)<svg viewBox="0 0 24 24"><path d="M4 8h16v12H4zM8 4h8v4M8 12h8"/></svg>@break
                                        @case(1)<svg viewBox="0 0 24 24"><path d="M12 2s-7 9-7 13a7 7 0 0 0 14 0c0-4-7-13-7-13Z"/></svg>@break
                                        @case(2)<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M12 7v5l4 2"/></svg>@break
                                        @case(3)<svg viewBox="0 0 24 24"><path d="M7 3c-3 4 3 5 0 9s-2 5 0 9M13 3c-3 4 3 5 0 9s-2 5 0 9M19 3c-3 4 3 5 0 9s-2 5 0 9"/></svg>@break
                                        @case(4)<svg viewBox="0 0 24 24"><path d="M4 5h16v15H4zM4 12h16M10 5v7M15 12v8"/></svg>@break
                                        @default<svg viewBox="0 0 24 24"><path d="M3 6h12v11H3zM15 9h4l3 4v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="19" cy="18" r="2"/></svg>
                                    @endswitch
                                </span>
                                <span class="enf-fase-nombre">{{ $fase['nombre'] }}</span>
                                <strong class="enf-fase-numero">{{ $fase['cantidad'] }}</strong>
                                <span class="enf-fase-unidad">{{ $fase['cantidad'] === 1 ? 'caja' : 'cajas' }}</span>
                                <div class="enf-fase-barra"><span style="width: {{ $fase['porcentaje'] }}%"></span></div>
                                <span class="enf-fase-porcentaje">{{ number_format($fase['porcentaje'], 1, ',', '.') }} %</span>
                            </article>
                        @endforeach
                    </div>
                    <p class="enf-bloque-pie">Los porcentajes representan la participación en el total de cajas activas, no el avance de esterilización.</p>
                </section>
            </div>

            <div class="enf-dos-columnas enf-zona-inferior">
                <section class="panel enf-bloque enf-pendientes" aria-labelledby="enf-pendientes-titulo">
                    <div class="enf-bloque-cabeza">
                        <div><div class="enf-titulo-con-icono"><span class="enf-mini-icono enf-mini-check" aria-hidden="true">✓</span><h2 id="enf-pendientes-titulo">Preparación del traspaso</h2></div>
                            <p>Lista de apoyo para revisar antes del cierre formal.</p></div>
                        <span class="enf-pendientes-conteo" id="enf-pendientes-conteo" aria-live="polite">0 de 4</span>
                    </div>
                    <div class="enf-checklist" id="enf-checklist">
                        <label class="enf-check-item"><input type="checkbox"><span class="enf-check-cuadro" aria-hidden="true"></span><span>Revisar alertas abiertas <small>{{ $d['alertas_total'] }} registradas en el resumen</small></span></label>
                        <label class="enf-check-item"><input type="checkbox"><span class="enf-check-cuadro" aria-hidden="true"></span><span>Informar cajas que requieren atención <small>{{ $d['criticas_total'] }} cajas urgentes o con demora</small></span></label>
                        <label class="enf-check-item"><input type="checkbox"><span class="enf-check-cuadro" aria-hidden="true"></span><span>Conciliar entregas del bloque <small>{{ $d['entregas_turno'] }} operaciones registradas</small></span></label>
                        <label class="enf-check-item"><input type="checkbox"><span class="enf-check-cuadro" aria-hidden="true"></span><span>Preparar observaciones y relevo <small>Completar en la pantalla Cierre de turno</small></span></label>
                    </div>
                    <div class="enf-checklist-footer">
                        <p>Estas marcas son una ayuda visual y no se guardan. El cierre definitivo se registra en su formulario.</p>
                        <a class="btn btn-ghost" href="{{ route('mockups.cierre_turno') }}">Ir al cierre <span aria-hidden="true">›</span></a>
                    </div>
                    @if ($d['motivo_bloqueo'] && !$d['ya_cerrado'])
                        <p class="enf-aviso-cierre">{{ $d['motivo_bloqueo'] }}</p>
                    @endif
                </section>

                <section class="panel enf-bloque enf-actividad" aria-labelledby="enf-actividad-titulo">
                    <div class="enf-bloque-cabeza">
                        <div><div class="enf-titulo-con-icono"><span class="enf-mini-icono enf-mini-reloj" aria-hidden="true">◷</span><h2 id="enf-actividad-titulo">Actividad reciente</h2></div>
                            <p>Movimientos y entregas registrados en el bloque vigente.</p></div>
                        <span class="enf-resumen-total">Hoy / turno actual</span>
                    </div>
                    <ol class="enf-timeline">
                        @forelse ($d['actividad'] as $accion)
                            <li>
                                <time>{{ $accion['hora'] }}</time>
                                <div><strong>{{ $accion['objeto'] !== '' ? $accion['objeto'].' · ' : '' }}{{ $accion['accion'] }}</strong>
                                    @if ($accion['detalle'] !== '')<p>{{ $accion['detalle'] }}</p>@endif
                                    <small>{{ $accion['actor'] }}</small>
                                </div>
                            </li>
                        @empty
                            <li class="enf-timeline-vacio"><span>No hay movimientos registrados en este bloque.</span></li>
                        @endforelse
                    </ol>
                    <p class="enf-bloque-pie">Información actualizada al cargar la página: {{ $d['momento'] }} (Chile).</p>
                </section>
            </div>
        @endif
    </main>
</div>
@endsection

@push('scripts')
<script>
// La lista es de revisión visual; las confirmaciones oficiales están en Cierre de turno.
(() => {
    const lista = document.getElementById('enf-checklist');
    const contador = document.getElementById('enf-pendientes-conteo');
    if (!lista || !contador) return;
    const checks = [...lista.querySelectorAll('input[type="checkbox"]')];
    const actualizar = () => {
        const completos = checks.filter(c => c.checked).length;
        contador.textContent = `${completos} de ${checks.length}`;
        lista.querySelectorAll('.enf-check-item').forEach((item, i) => {
            item.classList.toggle('enf-check-listo', checks[i].checked);
        });
    };
    checks.forEach(c => c.addEventListener('change', actualizar));
    actualizar();
})();
</script>
@endpush
