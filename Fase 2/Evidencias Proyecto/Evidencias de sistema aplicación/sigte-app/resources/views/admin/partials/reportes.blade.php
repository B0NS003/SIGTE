        <nav class="reportes-tabs" aria-label="Tipos de reportes" role="tablist">
            <button class="reportes-tab activo" type="button" role="tab" id="tab-produccion" aria-selected="true" aria-controls="panel-produccion" data-tab="produccion">Producción</button>
            <button class="reportes-tab" type="button" role="tab" id="tab-trazabilidad" aria-selected="false" aria-controls="panel-trazabilidad" data-tab="trazabilidad">Trazabilidad</button>
            <button class="reportes-tab" type="button" role="tab" id="tab-entregas" aria-selected="false" aria-controls="panel-entregas" data-tab="entregas">Entregas</button>
        </nav>

        <form class="panel reportes-filtros" method="GET" action="{{ route('mockups.panel', 'administradora') }}">
            <div class="reportes-section-head"><div><h2>Filtros del reporte</h2><p>Selecciona el período para consultar los registros disponibles.</p></div></div>
            <div class="reportes-campos reportes-campos-produccion">
                <label class="reportes-campo">Desde<input type="date" name="desde" value="{{ $filtros['desde'] }}" required></label>
                <label class="reportes-campo">Hasta<input type="date" name="hasta" value="{{ $filtros['hasta'] }}" required></label>
                <label class="reportes-campo reportes-filtro-tipo">Tipo de movimiento<select name="tipo">
                    <option value="">Todos los tipos</option>
                    @foreach ($tipos_auditoria as $clave => $nombre)
                        <option value="{{ $clave }}" @selected($filtros['tipo'] === $clave)>{{ $nombre }}</option>
                    @endforeach
                </select></label>
                <label class="reportes-campo reportes-filtro-servicio">Servicio<select name="servicio">
                    <option value="">Todos los servicios</option>
                    @foreach ($servicios_produccion as $servicio)
                        <option value="{{ $servicio->id }}" @selected(($filtros['servicio'] ?? null) === (int) $servicio->id)>{{ $servicio->nombre }}</option>
                    @endforeach
                </select></label>
                <button class="btn btn-dark reportes-consultar" type="submit">Consultar registros</button>
            </div>
            <p class="reportes-ayuda">Los litros se calculan desde los registros diarios de producción. El tipo de movimiento solo se aplica a Trazabilidad.</p>
        </form>

        @if ($falla)
            <div class="panel reportes-estado"><strong>No se pudo consultar la información.</strong><p>Revisa la conexión a la base de datos e inténtalo nuevamente.</p></div>
        @else
            @php
                $totalCajasFlujo = collect($en_flujo)->sum('cantidad');
                $coloresFlujo = ['#3998ed', '#78b9f5', '#f5c44b', '#f48aa4', '#a98add', '#9edb91'];
                $segmentosFlujo = [];
                $inicioFlujo = 0;
                foreach ($en_flujo as $indiceFlujo => $filaFlujo) {
                    $porcentajeFlujo = $totalCajasFlujo > 0 ? (float) $filaFlujo['cantidad'] / $totalCajasFlujo * 100 : 0;
                    $colorFlujo = $coloresFlujo[$indiceFlujo % count($coloresFlujo)];
                    $segmentosFlujo[] = $colorFlujo . ' ' . $inicioFlujo . '% ' . ($inicioFlujo + $porcentajeFlujo) . '%';
                    $inicioFlujo += $porcentajeFlujo;
                }
                $graficoFlujo = count($segmentosFlujo) ? 'conic-gradient(' . implode(', ', $segmentosFlujo) . ')' : 'conic-gradient(#e2e8f0 0% 100%)';
            @endphp
            <section class="reportes-panel activo" id="panel-produccion" role="tabpanel" aria-labelledby="tab-produccion">
                <div class="reportes-section-head reportes-titulo-modulo">
                    <div><h2>▥ &nbsp;Reporte de producción</h2><p>Procesamiento de cajas y producción mensual, presentados por separado.</p></div>
                    <span class="reportes-chip">Vista previa</span>
                </div>

                <article class="panel reportes-bloque reportes-bloque-moderno">
                    <div class="reportes-banda"><h3><span class="reportes-banda-icono"><svg class="reportes-icono " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg></span><span>1. Procesamiento de cajas</span></h3><span class="reportes-banda-descripcion">Estado actual del flujo · No corresponde a movimientos históricos del período</span></div>
                    <div class="reportes-produccion-grid">
                        <div class="reportes-produccion-principal">
                            <div class="reportes-resumen reportes-resumen-compacto">
                                <div class="reportes-kpi reportes-kpi-azul"><svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg><div class="reportes-kpi-texto"><span>Cajas actualmente en flujo</span><strong>{{ $totalCajasFlujo }}</strong></div></div>
                                <div class="reportes-kpi reportes-kpi-verde"><svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/><path d="m9 16 2 2 4-4"/></svg><div class="reportes-kpi-texto"><span>Cajas recibidas en el período</span><strong>—</strong><small>Pendiente de vinculación</small></div></div>
                                <div class="reportes-kpi reportes-kpi-violeta"><svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 17l9 5 9-5"/></svg><div class="reportes-kpi-texto"><span>Etapas con cajas</span><strong>{{ count($en_flujo) }}</strong></div></div>
                            </div>
                            <h4 class="reportes-titulo-detalle">Cajas por etapa (flujo actual)</h4>
                            <div class="reportes-etapas" aria-label="Etapas del procesamiento">
                                @foreach (['Recepción', 'Lavado', 'Preparación', 'Esterilización', 'Almacén', 'Entrega'] as $indiceEtapa => $nombreEtapa)
                                    @php
                                        $filaEtapa = collect($en_flujo)->first(fn ($item) => mb_strtolower(trim($item['etapa'])) === mb_strtolower($nombreEtapa));
                                    @endphp
                                    <div class="reportes-etapa reportes-etapa-{{ $indiceEtapa }}">
@if ($indiceEtapa === 0)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg>@endif
@if ($indiceEtapa === 1)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22a8 8 0 0 0 8-8c0-4-8-12-8-12S4 10 4 14a8 8 0 0 0 8 8Z"/></svg>@endif
@if ($indiceEtapa === 2)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="m8.12 8.12 12.02 12.02M14.8 9.2 20.14 3.86M8.12 15.88 12 12"/></svg>@endif
@if ($indiceEtapa === 3)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3c-4 4 4 5 0 9s-2 5 0 9M14 3c-4 4 4 5 0 9s-2 5 0 9M20 3c-4 4 4 5 0 9s-2 5 0 9"/></svg>@endif
@if ($indiceEtapa === 4)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="1"/><path d="M3 12h18M8 5v7M16 12v9M7 16h5"/></svg>@endif
@if ($indiceEtapa === 5)<svg class="reportes-icono reportes-etapa-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h11v12H3zM14 9h4l3 4v4h-7z"/><circle cx="7.5" cy="18" r="2"/><circle cx="17.5" cy="18" r="2"/></svg>@endif
<div class="reportes-etapa-texto"><span>{{ $nombreEtapa }}</span><strong>{{ $filaEtapa['cantidad'] ?? 0 }}</strong></div></div>
                                    @if ($indiceEtapa < 5)<span class="reportes-etapa-flecha" aria-hidden="true">›</span>@endif
                                @endforeach
                            </div>
                            <div class="reportes-tabla reportes-tabla-compacta"><table><thead><tr><th>Etapa actual</th><th>Cantidad de cajas</th><th>% del total</th></tr></thead><tbody>
                                @forelse ($en_flujo as $indice => $fila)
                                    <tr><td><span class="reportes-punto" style="background: {{ $coloresFlujo[$indice % count($coloresFlujo)] }}"></span>{{ $fila['etapa'] }}</td><td>{{ $fila['cantidad'] }}</td><td>{{ $totalCajasFlujo ? number_format($fila['cantidad'] / $totalCajasFlujo * 100, 1, ',', '.') : 0 }} %</td></tr>
                                @empty <tr><td colspan="3" class="reportes-vacio">No hay cajas actualmente en flujo.</td></tr> @endforelse
                            </tbody></table></div>
                        </div>
                        <div class="reportes-distribucion">
                            <h4 class="reportes-titulo-detalle">Distribución de cajas en el flujo</h4>
                            <div class="reportes-grafico-area">
                                <div class="reportes-dona" style="background: {{ $graficoFlujo }}" role="img" aria-label="Distribución de {{ $totalCajasFlujo }} cajas entre {{ count($en_flujo) }} etapas"><div class="reportes-dona-centro"><strong>{{ $totalCajasFlujo }}</strong><span>cajas</span></div></div>
                                <div class="reportes-leyenda">
                                    @forelse ($en_flujo as $indice => $fila)
                                        <div><span class="reportes-punto" style="background: {{ $coloresFlujo[$indice % count($coloresFlujo)] }}"></span><span>{{ $fila['etapa'] }}</span><strong>{{ $fila['cantidad'] }}</strong></div>
                                    @empty <p class="reportes-nota">Sin datos disponibles.</p> @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="panel reportes-bloque reportes-bloque-moderno">
                    <div class="reportes-banda"><h3><span class="reportes-banda-icono"><svg class="reportes-icono " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6M10 3v7l-6 9a2 2 0 0 0 2 3h12a2 2 0 0 0 2-3l-6-9V3M8 16h8"/></svg></span> 2. Producción mensual por servicio</h3><span>Consumos y litros registrados por secretaría, agrupados por mes</span></div>
                    <div class="reportes-resumen reportes-resumen-compacto reportes-produccion-kpis">
                        <div class="reportes-kpi reportes-kpi-azul"><div class="reportes-kpi-texto"><span>Servicios con registros</span><strong>{{ number_format($produccion['servicios'], 0, ',', '.') }}</strong></div></div>
                        <div class="reportes-kpi reportes-kpi-verde"><div class="reportes-kpi-texto"><span>Unidades procesadas</span><strong>{{ number_format($produccion['cantidad'], 0, ',', '.') }}</strong></div></div>
                        <div class="reportes-kpi reportes-kpi-violeta"><div class="reportes-kpi-texto"><span>Litros totales</span><strong>{{ number_format($produccion['litros'], 2, ',', '.') }} L</strong></div></div>
                        <div class="reportes-kpi reportes-kpi-naranja"><div class="reportes-kpi-texto"><span>Materiales distintos</span><strong>{{ number_format($produccion['materiales'], 0, ',', '.') }}</strong></div></div>
                    </div>
                    <div class="reportes-produccion-analitica">
                        <div class="reportes-produccion-card">
                            <h4 class="reportes-titulo-detalle">Litros por servicio (período)</h4>
                            @php $maxLitrosServicio = max(1, (float) $produccion['por_servicio']->max('litros')); @endphp
                            @forelse ($produccion['por_servicio'] as $servicio)
                                <div class="reportes-produccion-barra-fila">
                                    <span title="{{ $servicio->nombre }}">{{ $servicio->nombre }}</span>
                                    <div class="reportes-produccion-barra-fondo"><div style="width: {{ round(((float) $servicio->litros / $maxLitrosServicio) * 100, 2) }}%"></div></div>
                                    <strong>{{ number_format($servicio->litros, 2, ',', '.') }} L</strong>
                                </div>
                            @empty
                                <p class="reportes-vacio">No hay registros de producción para este período.</p>
                            @endforelse
                        </div>
                        <div class="reportes-produccion-card">
                            <h4 class="reportes-titulo-detalle">Evolución mensual de litros</h4>
                            @php $maxLitrosMes = max(1, (float) $produccion['por_mes']->max('litros')); @endphp
                            @if ($produccion['por_mes']->isNotEmpty())
                                <div class="reportes-produccion-meses">
                                    @foreach ($produccion['por_mes'] as $mes)
                                        <div class="reportes-produccion-mes" title="{{ $mes->mes }}: {{ number_format($mes->litros, 2, ',', '.') }} litros">
                                            <strong>{{ number_format($mes->litros, 0, ',', '.') }} L</strong>
                                            <div class="reportes-produccion-mes-columna"><span style="height: {{ max(2, round(((float) $mes->litros / $maxLitrosMes) * 100, 2)) }}%"></span></div>
                                            <small>{{ $mes->mes }}</small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="reportes-vacio">No hay registros para graficar en el período seleccionado.</p>
                            @endif
                        </div>
                    </div>
                    <div class="reportes-produccion-card reportes-produccion-detalle">
                        <h4 class="reportes-titulo-detalle">Detalle mensual por servicio</h4>
                        <div class="reportes-tabla reportes-tabla-compacta">
                            <table>
                                <thead><tr><th>Mes</th><th>Servicio</th><th>Unidades</th><th>Litros</th></tr></thead>
                                <tbody>
                                    @forelse ($produccion['detalle'] as $fila)
                                        <tr>
                                            <td>{{ $fila->mes }}</td>
                                            <td>{{ $fila->servicio }}</td>
                                            <td>{{ number_format($fila->cantidad, 0, ',', '.') }}</td>
                                            <td>{{ number_format($fila->litros, 2, ',', '.') }} L</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="reportes-vacio">No hay registros de producción para las fechas y el servicio seleccionados.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </article>
            </section>

            <section class="reportes-panel" id="panel-trazabilidad" role="tabpanel" aria-labelledby="tab-trazabilidad" hidden>
                @php
                    // Resumen calculado exclusivamente con los registros de auditoría ya entregados por el controlador.
                    $trazaRegistros = collect($movimientos);
                    $trazaTotal = $trazaRegistros->count();
                    $trazaTipos = $trazaRegistros->groupBy(fn ($m) => trim((string) ($m['tipo_etiqueta'] ?? 'Sin tipo')));
                    $trazaPaleta = ['#4e74d9', '#78b9f5', '#f5c44b', '#f48aa4', '#a98add', '#9edb91', '#f3a66e'];
                    $trazaSegmentos = [];
                    $trazaAcumulado = 0;
                    $trazaIndiceColor = 0;
                    foreach ($trazaTipos as $grupoTipo) {
                        $trazaParte = $trazaTotal ? $grupoTipo->count() / $trazaTotal * 100 : 0;
                        $trazaColor = $trazaPaleta[$trazaIndiceColor % count($trazaPaleta)];
                        $trazaSegmentos[] = $trazaColor . ' ' . $trazaAcumulado . '% ' . ($trazaAcumulado + $trazaParte) . '%';
                        $trazaAcumulado += $trazaParte;
                        $trazaIndiceColor++;
                    }
                    $trazaDona = $trazaTotal ? 'conic-gradient(' . implode(', ', $trazaSegmentos) . ')' : '#e2e8f0';
                    $trazaPorDia = $trazaRegistros->groupBy(fn ($m) => (string) ($m['fecha'] ?? 'Sin fecha'))->map->count();
                    $trazaMaxDia = max(1, (int) $trazaPorDia->max());
                @endphp
                <div class="reportes-section-head reportes-traza-encabezado">
                    <div class="reportes-traza-titulo">
                        <svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="4" r="3"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><path d="M10.5 6.7 6.5 16M13.5 6.7l4 9.3M8 19h8"/></svg>
                        <div><h2>Reporte de trazabilidad</h2><p>Acciones de auditoría registradas dentro del período seleccionado.</p></div>
                    </div>
                    <span class="reportes-chip">Vista previa</span>
                </div>

                <article class="panel reportes-traza-panel">
                    <div class="reportes-traza-grid">
                        <div class="reportes-traza-izquierda">
                            <div class="reportes-resumen reportes-traza-kpis">
                                <div class="reportes-kpi reportes-kpi-azul">
                                    <svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="4" width="14" height="18" rx="2"/><rect x="9" y="2" width="6" height="4" rx="1"/><path d="M9 11h6M9 15h6M9 19h4"/></svg>
                                    <div class="reportes-kpi-texto"><span>Registros mostrados</span><strong>{{ $trazaTotal }}</strong></div>
                                </div>
                                <div class="reportes-kpi reportes-kpi-violeta">
                                    <svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 17l9 5 9-5"/></svg>
                                    <div class="reportes-kpi-texto"><span>Tipos encontrados</span><strong>{{ $trazaTipos->count() }}</strong></div>
                                </div>
                                <div class="reportes-kpi reportes-kpi-naranja">
                                    <svg class="reportes-icono reportes-kpi-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 0 1 15-6l3 3M21 3v6h-6M21 12a9 9 0 0 1-15 6l-3-3M3 21v-6h6"/></svg>
                                    <div class="reportes-kpi-texto"><span>Retrocesos estructurados</span><strong>—</strong><small>Pendiente de integración</small></div>
                                </div>
                            </div>
                            <div class="reportes-traza-serie">
                                <h3>Registros por fecha</h3>
                                @if ($trazaPorDia->isNotEmpty())
                                    <div class="reportes-traza-barras" role="img" aria-label="Cantidad de registros de auditoría por fecha">
                                        @foreach ($trazaPorDia as $dia => $cantidad)
                                            <div class="reportes-traza-barra-col" title="{{ $dia }}: {{ $cantidad }} registros">
                                                <strong>{{ $cantidad }}</strong>
                                                <div class="reportes-traza-barra" style="height: {{ max(5, round($cantidad / $trazaMaxDia * 100)) }}%"></div>
                                                <span>{{ $dia }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="reportes-traza-sin-datos">No hay registros en el período seleccionado.</div>
                                @endif
                            </div>
                        </div>
                        <div class="reportes-traza-derecha">
                            <h3>Movimientos por tipo</h3>
                            <div class="reportes-traza-distribucion">
                                <div class="reportes-dona reportes-traza-dona" style="background: {{ $trazaDona }}" role="img" aria-label="{{ $trazaTotal }} registros distribuidos por tipo">
                                    <div class="reportes-dona-centro"><strong>{{ $trazaTotal }}</strong><span>registros</span></div>
                                </div>
                                <div class="reportes-traza-leyenda">
                                    @forelse ($trazaTipos as $tipo => $grupo)
                                        <div><span class="reportes-punto" style="background: {{ $trazaPaleta[$loop->index % count($trazaPaleta)] }}"></span><span>{{ $tipo }}</span><strong>{{ $trazaTotal ? number_format($grupo->count() / $trazaTotal * 100, 1, ',', '.') : 0 }} %</strong></div>
                                    @empty
                                        <p class="reportes-nota">Sin movimientos registrados.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="panel reportes-bloque reportes-traza-detalle">
                    <div class="reportes-traza-detalle-cabecera">
                        <svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 6h12M9 12h12M9 18h12"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/></svg>
                        <div><h3>Detalle de movimientos</h3><p class="reportes-nota">Vista preliminar: el controlador actual limita los movimientos a 500 registros.</p></div>
                    </div>
                    <div class="reportes-tabla reportes-traza-tabla"><table><thead><tr><th>Fecha</th><th>Hora</th><th>Usuario</th><th>Acción</th><th>Tipo</th><th>Objeto</th><th>Detalle</th></tr></thead><tbody>
                        @forelse ($movimientos as $fila)
                            <tr><td>{{ $fila['fecha'] }}</td><td>{{ $fila['hora'] }}</td><td><span class="reportes-traza-usuario"><span class="reportes-traza-avatar">{{ mb_strtoupper(mb_substr((string) ($fila['actor'] ?? '?'), 0, 1)) }}</span>{{ $fila['actor'] }}</span></td><td><span class="reportes-traza-accion">{{ $fila['accion'] }}</span></td><td>{{ $fila['tipo_etiqueta'] }}</td><td>{{ $fila['objeto'] }}</td><td>{{ $fila['detalle'] }}</td></tr>
                        @empty <tr><td colspan="7" class="reportes-vacio">No hay movimientos de auditoría para estos filtros.</td></tr> @endforelse
                    </tbody></table></div>
                </article>
            </section>

            <section class="reportes-panel reportes-entregas" id="panel-entregas" role="tabpanel" aria-labelledby="tab-entregas" hidden>
                @php
                    $entregasListado = collect($entregas);
                    $entregasCantidad = $entregasListado->count();
                    $entregasServicios = $entregasListado->groupBy(fn ($fila) => trim((string) ($fila['servicio'] ?? 'Sin servicio')) ?: 'Sin servicio')
                        ->map->count()->sortDesc();
                    $entregasServiciosValidos = $entregasServicios->keys()->filter(fn ($nombre) => $nombre !== 'Sin servicio')->count();
                    $entregasColores = ['#398ff3', '#f5bc40', '#f48cac', '#a877ec', '#36b8a5', '#f18a51'];
                    $entregasPorFecha = collect();
                    foreach ($entregasListado as $registroEntrega) {
                        $fechaTexto = trim((string) ($registroEntrega['cuando'] ?? ''));
                        $fechaClave = null;
                        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $fechaTexto, $coincidencia)) {
                            $fechaClave = $coincidencia[1] . '-' . $coincidencia[2] . '-' . $coincidencia[3];
                        } elseif (preg_match('/^(\d{2})[\/-](\d{2})[\/-](\d{4})/', $fechaTexto, $coincidencia)) {
                            $fechaClave = $coincidencia[3] . '-' . $coincidencia[2] . '-' . $coincidencia[1];
                        }
                        if ($fechaClave && \Carbon\Carbon::hasFormat($fechaClave, 'Y-m-d')) {
                            $entregasPorFecha->put($fechaClave, ($entregasPorFecha->get($fechaClave, 0)) + 1);
                        }
                    }
                    $entregasPorFecha = $entregasPorFecha->sortKeys();
                    $entregasDiaMaximo = $entregasPorFecha->sortDesc()->first() ?? 0;
                    $entregasFechaMaxima = $entregasPorFecha->sortDesc()->keys()->first();
                    $entregasDiasConActividad = $entregasPorFecha->count();
                    $entregasPromedio = $entregasDiasConActividad ? $entregasCantidad / $entregasDiasConActividad : 0;
                    $entregasInicio = \Carbon\Carbon::parse($filtros['desde'])->startOfDay();
                    $entregasFin = \Carbon\Carbon::parse($filtros['hasta'])->startOfDay();
                    $entregasTotalDias = $entregasInicio->diffInDays($entregasFin) + 1;
                    $entregasMostrarCalendario = $entregasTotalDias >= 1 && $entregasTotalDias <= 42;
                @endphp

                <div class="reportes-section-head reportes-entregas-encabezado">
                    <div class="reportes-entregas-titulo">
                        <svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h11v12H3zM14 9h4l3 4v4h-7z"/><circle cx="7.5" cy="18" r="2"/><circle cx="17.5" cy="18" r="2"/></svg>
                        <div><h2>Reporte de entregas</h2><p>Cajas de instrumental quirúrgico entregadas a los servicios.</p></div>
                    </div>
                    <span class="reportes-chip">Vista previa</span>
                </div>

                <div class="reportes-entregas-kpis">
                    <div class="reportes-entregas-kpi reportes-entregas-kpi-principal">
                        <img class="reportes-entregas-bandeja" src="{{ asset('images/Reportes/reportes_entrega_decoracion_1.png') }}" alt="Caja metálica de esterilización para instrumental quirúrgico">
                        <div class="reportes-entregas-kpi-contenido"><span>Cajas entregadas</span><strong>{{ $entregasCantidad }}</strong><small>Registros mostrados en el período</small></div>
                    </div>
                    <div class="reportes-entregas-kpi reportes-entregas-kpi-operaciones">
                        <svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="4" width="14" height="18" rx="2"/><rect x="9" y="2" width="6" height="4" rx="1"/><path d="m9 14 2 2 4-4"/></svg>
                        <div><span>Operaciones de entrega</span><strong>—</strong><small>Pendiente de vincular Entrega</small></div>
                    </div>
                    <div class="reportes-entregas-kpi reportes-entregas-kpi-servicios">
                        <svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M17 14a5 5 0 0 1 4 5v1"/></svg>
                        <div><span>Servicios identificados</span><strong>{{ $entregasServiciosValidos }}</strong><small>Según registros mostrados</small></div>
                    </div>
                </div>

                <div class="reportes-entregas-analitica">
                    <article class="panel reportes-entregas-card">
                        <h3><svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><rect x="3" y="9" width="7" height="12"/><rect x="14" y="3" width="7" height="18"/><path d="M5 13h3M16 7h3"/></svg> Entregas por servicio</h3>
                        @forelse ($entregasServicios as $nombreServicio => $cantidadServicio)
                            @php $porcentajeServicio = $entregasCantidad ? $cantidadServicio / $entregasCantidad * 100 : 0; $colorServicio = $entregasColores[$loop->index % count($entregasColores)]; @endphp
                            <div class="reportes-entregas-servicio-fila">
                                <span class="reportes-entregas-servicio-nombre"><i style="background: {{ $colorServicio }}"></i>{{ $nombreServicio }}</span>
                                <span class="reportes-entregas-servicio-cantidad">{{ $cantidadServicio }} {{ $cantidadServicio === 1 ? 'caja' : 'cajas' }}</span>
                                <div class="reportes-entregas-progreso"><span style="width: {{ $porcentajeServicio }}%; background: {{ $colorServicio }}"></span></div>
                                <strong>{{ number_format($porcentajeServicio, 0) }} %</strong>
                            </div>
                        @empty
                            <div class="reportes-entregas-vacio">No hay entregas por servicio en el período seleccionado.</div>
                        @endforelse
                    </article>

                    <article class="panel reportes-entregas-card">
                        <h3><svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18"/></svg> Entregas por día (período)</h3>
                        @if ($entregasMostrarCalendario)
                            <div class="reportes-entregas-calendario" aria-label="Actividad diaria de entregas">
                                @for ($diaIndice = 0; $diaIndice < $entregasTotalDias; $diaIndice++)
                                    @php $diaCalendario = $entregasInicio->copy()->addDays($diaIndice); $diaClave = $diaCalendario->format('Y-m-d'); $cantidadDia = $entregasPorFecha->get($diaClave, 0); $intensidadDia = $entregasDiaMaximo ? $cantidadDia / $entregasDiaMaximo : 0; @endphp
                                    <div class="reportes-entregas-dia" title="{{ $diaCalendario->format('d/m/Y') }}: {{ $cantidadDia }} cajas" style="--entregas-intensidad: {{ $intensidadDia }}" aria-label="{{ $diaCalendario->format('d/m/Y') }}: {{ $cantidadDia }} cajas"><span>{{ $diaCalendario->format('d') }}</span></div>
                                @endfor
                            </div>
                            <p class="reportes-entregas-leyenda-calendario">Menor actividad <span></span><span></span><span></span><span></span> Mayor actividad</p>
                        @else
                            <div class="reportes-entregas-vacio">Para períodos de más de 42 días, consulta el detalle de entregas.</div>
                        @endif
                        <div class="reportes-entregas-datos-dia">
                            <div><span>Día con más entregas</span><strong>{{ $entregasFechaMaxima ? \Carbon\Carbon::parse($entregasFechaMaxima)->format('d/m/Y') : '—' }}</strong><small>{{ $entregasDiaMaximo }} cajas</small></div>
                            <div><span>Promedio por día con actividad</span><strong>{{ $entregasDiasConActividad ? number_format($entregasPromedio, 1, ',', '.') : '—' }}</strong><small>Sobre registros mostrados</small></div>
                        </div>
                    </article>
                </div>

                <div class="reportes-entregas-inferior">
                    <article class="panel reportes-entregas-card reportes-entregas-ultimas">
                        <div class="reportes-entregas-card-cabecera"><h3><svg class="reportes-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> Últimas entregas registradas</h3><span class="reportes-chip">{{ $entregasCantidad }} mostradas</span></div>
                        <div class="reportes-tabla reportes-entregas-tabla"><table><thead><tr><th>Código de caja</th><th>Servicio</th><th>Fecha de entrega</th><th>Responsable</th></tr></thead><tbody>
                            @forelse ($entregasListado as $fila)
                                <tr><td><span class="reportes-entregas-codigo">{{ $fila['codigo'] }}</span></td><td>{{ $fila['servicio'] }}</td><td>{{ $fila['cuando'] }}</td><td><span class="reportes-traza-usuario"><span class="reportes-traza-avatar">{{ mb_strtoupper(mb_substr((string) ($fila['responsable'] ?? '?'), 0, 1)) }}</span>{{ $fila['responsable'] }}</span></td></tr>
                            @empty <tr><td colspan="4" class="reportes-vacio">No hay cajas entregadas para este período.</td></tr> @endforelse
                        </tbody></table></div>
                        <p class="reportes-nota">Vista preliminar: el controlador limita el listado a 100 cajas. Los totales representan únicamente los registros mostrados.</p>
                    </article>
                    <article class="panel reportes-entregas-card reportes-entregas-estado">
                        <div class="reportes-entregas-estado-interior">
                            <img class="reportes-entregas-bandeja-estado" src="{{ asset('images/Reportes/reportes_entrega_decoracion_2.png') }}" alt="Bandeja metálica de esterilización con instrumental quirúrgico">
                            <div><h3>Estado del período</h3><p>{{ $entregasCantidad > 0 ? 'Se encontraron cajas de instrumental quirúrgico registradas como entregadas en el período seleccionado.' : 'No se encontraron cajas registradas como entregadas para el período seleccionado.' }}</p><small>La validación de operaciones completas estará disponible al integrar Entrega y EntregaCaja.</small></div>
                        </div>
                    </article>
                </div>
            </section>

            <footer class="panel reportes-pie">
                <div><strong>Hospital San José de Melipilla</strong><p>Generado para consulta por {{ $usuario['nombre'] }} · {{ $usuario['rol'] }} — {{ $fecha }} a las {{ $hora }}</p><p>Período seleccionado: {{ $filtros['desde'] }} al {{ $filtros['hasta'] }}</p></div>
                <div class="reportes-exportar"><button class="btn" type="button" disabled title="Se habilitará al implementar la exportación XLSX">Exportar Excel</button><button class="btn" type="button" disabled title="Se habilitará al implementar la exportación PDF">Exportar PDF</button></div>
            </footer>
        @endif
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.reportes-tab');
    const panels = document.querySelectorAll('.reportes-panel');
    function ajustarFiltros(tab) {
        const produccion = tab === 'produccion';
        const tipo = document.querySelector('.reportes-filtro-tipo');
        const servicio = document.querySelector('.reportes-filtro-servicio');
        if (tipo) tipo.hidden = tab !== 'trazabilidad';
        if (servicio) servicio.hidden = !produccion;
    }
    ajustarFiltros('produccion');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            ajustarFiltros(tab.dataset.tab);
            tabs.forEach(function (item) {
                const active = item === tab;
                item.classList.toggle('activo', active);
                item.setAttribute('aria-selected', String(active));
            });
            panels.forEach(function (panel) {
                const active = panel.id === 'panel-' + tab.dataset.tab;
                panel.classList.toggle('activo', active);
                panel.hidden = !active;
            });
        });
    });
});
</script>
@endpush
