@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}?v={{ @filemtime(public_path('css/reportes.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/secretaria.css') }}?v={{ @filemtime(public_path('css/secretaria.css')) }}">
@endpush

@section('title', 'SIGTE — Producción y litros')

@section('content')
<div class="shell">
    @include('secretaria.partials.sidebar')

    <main class="main reportes-secretaria sp-produccion">
        <header class="main-top sp-produccion-top">
            <div>
                <p class="eyebrow">Gestión de producción</p>
                <h1>Producción y litros</h1>
                <p class="main-sub">Registra la producción diaria, administra los consumos mensuales históricos y consulta los registros almacenados.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'secretaria') }}">Volver al inicio</a>
            </div>
        </header>

        @php
            // Cada pestaña tiene una URL propia. Esto permite volver a la misma pestaña
            // luego de guardar, actualizar un registro mensual o cambiar de página.
            $seccionSolicitada = request()->query('seccion');
            $pestanasPermitidas = ['diario', 'mensual', 'registros'];
            if (in_array($seccionSolicitada, $pestanasPermitidas, true)) {
                $pestanaProduccion = $seccionSolicitada;
            } elseif (old('periodo') || $editando || (request()->query('servicio') && request()->query('periodo'))) {
                $pestanaProduccion = 'mensual';
            } elseif (request()->has('page')) {
                $pestanaProduccion = 'registros';
            } else {
                $pestanaProduccion = 'diario';
            }
        @endphp

        <nav class="sp-secciones-nav" aria-label="Secciones de producción">
            <a href="{{ route('secretaria.produccion', ['seccion' => 'diario']) }}" class="sp-seccion-tab {{ $pestanaProduccion === 'diario' ? 'activo' : '' }}" @if($pestanaProduccion === 'diario') aria-current="page" @endif>Registro diario</a>
            <a href="{{ route('secretaria.produccion', ['seccion' => 'mensual']) }}" class="sp-seccion-tab {{ $pestanaProduccion === 'mensual' ? 'activo' : '' }}" @if($pestanaProduccion === 'mensual') aria-current="page" @endif>Registro mensual</a>
            <a href="{{ route('secretaria.produccion', ['seccion' => 'registros']) }}" class="sp-seccion-tab {{ $pestanaProduccion === 'registros' ? 'activo' : '' }}" @if($pestanaProduccion === 'registros') aria-current="page" @endif>Producciones registradas</a>
        </nav>

        @if(session('ok'))
            <div class="sp-alerta sp-alerta-ok" role="status">{{ session('ok') }}</div>
        @endif
        @if($errors->any())
            <div class="sp-alerta sp-alerta-error" role="alert">
                <strong>Revisa los campos:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($pestanaProduccion === 'diario')
        <div class="sp-grid sp-produccion-formulario">
            <section class="panel sp-card sp-card-principal" aria-labelledby="sp-nuevo-titulo">
                <div class="sp-encabezado">
                    <div>
                        <p class="sp-seccion-etiqueta">CARGA DE PRODUCCIÓN</p>
                        <h2 id="sp-nuevo-titulo">Nuevo registro diario</h2>
                        <p>Completa los datos correspondientes al material procesado.</p>
                    </div>
                    <span class="sp-etiqueta-diaria">Por día y material</span>
                </div>

                <form method="POST" action="{{ route('secretaria.produccion.guardar') }}" class="sp-form">
                    @csrf
                    <div class="sp-form-grid">
                        <div class="sp-campo">
                            <label for="fecha">Fecha <span>*</span></label>
                            <input id="fecha" type="date" name="fecha" value="{{ old('fecha', request()->query('fecha', now()->toDateString())) }}" max="{{ now()->toDateString() }}" required>
                            @error('fecha')<small class="sp-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="sp-campo">
                            <label for="servicio_id">Servicio clínico <span>*</span></label>
                            <select id="servicio_id" name="servicio_id" required>
                                <option value="">Selecciona un servicio</option>
                                @foreach($servicios_produccion as $servicio)
                                    <option value="{{ $servicio->id }}" @selected((string) old('servicio_id', request()->query('servicio_id', '')) === (string) $servicio->id)>{{ $servicio->nombre }}</option>
                                @endforeach
                            </select>
                            @error('servicio_id')<small class="sp-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="sp-campo sp-ancho">
                            <label for="material_id">Material <span>*</span></label>
                            <select id="material_id" name="material_id" required>
                                <option value="">Selecciona un material</option>
                                @foreach($materiales_produccion->groupBy('categoria') as $categoria => $materiales)
                                    <optgroup label="{{ ucfirst(str_replace('_', ' ', $categoria)) }}">
                                        @foreach($materiales as $material)
                                            <option value="{{ $material->id }}" @selected((string) old('material_id') === (string) $material->id)>{{ $material->nombre }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('material_id')<small class="sp-error">{{ $message }}</small>@enderror
                            @if($materiales_produccion->isEmpty())
                                <small class="sp-aviso">Aún no hay materiales activos en el catálogo. Debes cargarlos antes de registrar producción.</small>
                            @endif
                        </div>
                        <div class="sp-campo">
                            <label for="cantidad">Cantidad (unidades) <span>*</span></label>
                            <input id="cantidad" type="number" name="cantidad" min="1" step="1" value="{{ old('cantidad') }}" placeholder="Ej.: 12" required>
                            @error('cantidad')<small class="sp-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="sp-campo">
                            <label for="factor_litros">Factor (L por unidad) <span>*</span></label>
                            <input id="factor_litros" type="number" name="factor_litros" min="0.01" max="99999999.99" step="0.01" value="{{ old('factor_litros') }}" placeholder="Según tabla validada" required>
                            @error('factor_litros')<small class="sp-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="sp-campo sp-ancho">
                            <label for="observacion">Observación (opcional)</label>
                            <textarea id="observacion" name="observacion" maxlength="255" rows="3" placeholder="Información adicional del registro">{{ old('observacion') }}</textarea>
                        </div>
                    </div>
                    <div class="sp-nota-factor">
                        <strong>Equivalencia en litros</strong>
                        <span>Litros equivalentes = cantidad × factor (L por unidad). Utiliza el factor autorizado por el servicio; no se asigna automáticamente.</span>
                    </div>
                    <div class="sp-acciones">
                        <a class="btn btn-ghost" href="{{ route('secretaria.produccion') }}">Limpiar</a>
                        <button type="submit" class="btn btn-primary" @disabled($materiales_produccion->isEmpty())>Guardar producción</button>
                    </div>
                </form>
            </section>

            <aside class="sp-lateral" aria-label="Orientación para registrar producción">
                <section class="panel sp-card sp-guia">
                    <p class="sp-seccion-etiqueta">AYUDA DE REGISTRO</p>
                    <h2>Antes de guardar</h2>
                    <ol class="sp-guia-pasos">
                        <li><span>1</span><p>Selecciona el día y el servicio clínico.</p></li>
                        <li><span>2</span><p>Elige el material y registra su cantidad.</p></li>
                        <li><span>3</span><p>Ingresa el factor de litros validado para ese material.</p></li>
                    </ol>
                    <div class="sp-guia-aviso">
                        <strong>¿Qué significa el factor?</strong>
                        <p>Es la equivalencia en litros de una unidad de material. No corresponde al total de litros.</p>
                    </div>
                    <a class="sp-guia-enlace" href="{{ route('secretaria.reportes') }}">Consultar reportes <span aria-hidden="true">›</span></a>
                </section>
            </aside>
        </div>

        @elseif($pestanaProduccion === 'mensual')
        {{-- Mismo formato de dos columnas que Registro diario; sin segunda tabla duplicada. --}}
        <div class="sp-grid sp-produccion-formulario sp-mensual-grid">
            <section class="panel sp-card sp-card-principal sp-mensual-formulario" id="registro-mensual-formulario" aria-labelledby="sp-form-mensual-titulo">
                <div class="sp-encabezado">
                    <div>
                        <p class="sp-seccion-etiqueta">CARGA MENSUAL</p>
                        <h2 id="sp-form-mensual-titulo">{{ $editando ? 'Actualizar registro mensual' : 'Nuevo registro mensual' }}</h2>
                        <p>Registra totales consolidados cuando no exista un desglose diario para ese consumo.</p>
                    </div>
                    <span class="sp-etiqueta-diaria">Por mes y servicio</span>
                </div>
                <form method="POST" action="{{ route('secretaria.produccion.guardar') }}" class="sp-form">
                    @csrf
                    <div class="sp-form-grid">
                        <div class="sp-campo">
                            <label for="servicio_mensual_id">Servicio clínico <span>*</span></label>
                            <select id="servicio_mensual_id" name="servicio_id" required>
                                <option value="">Selecciona un servicio</option>
                                @foreach($servicios_produccion as $servicio)
                                    <option value="{{ $servicio->id }}" @selected((string) old('servicio_id', $editando->servicio_id ?? '') === (string) $servicio->id)>{{ $servicio->nombre }}</option>
                                @endforeach
                            </select>
                            @error('servicio_id')<small class="sp-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="sp-campo">
                            <label for="periodo">Mes <span>*</span></label>
                            <input id="periodo" name="periodo" type="month" required value="{{ old('periodo', $periodo_form) }}">
                            @error('periodo')<small class="sp-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="sp-campo">
                            <label for="consumo">Consumo (unidades) <span>*</span></label>
                            <input id="consumo" name="consumo" type="number" min="0" step="1" value="{{ old('consumo', $editando->consumo ?? '') }}" placeholder="Ej.: 50" required>
                            @error('consumo')<small class="sp-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="sp-campo">
                            <label for="litros">Litros totales <span>*</span></label>
                            <input id="litros" name="litros" type="number" min="0" step="0.01" value="{{ old('litros', $editando->litros ?? '') }}" placeholder="Ej.: 150.00" required>
                            @error('litros')<small class="sp-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="sp-campo sp-ancho">
                            <label for="observacion_mensual">Observación (opcional)</label>
                            <textarea id="observacion_mensual" name="observacion" rows="3" maxlength="255" placeholder="Información adicional del registro mensual">{{ old('observacion', $editando->observacion ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="sp-acciones">
                        <a class="btn btn-ghost" href="{{ route('secretaria.produccion', ['seccion' => 'mensual', 'anio' => $anio_seleccionado, 'mes' => $mes_seleccionado]) }}#registro-mensual-formulario">Limpiar</a>
                        <button type="submit" class="btn btn-primary">{{ $editando ? 'Guardar actualización' : 'Guardar consumo mensual' }}</button>
                    </div>
                </form>
            </section>
            <aside class="sp-lateral" aria-label="Orientación para registrar consumos mensuales">
                <section class="panel sp-card sp-guia">
                    <p class="sp-seccion-etiqueta">AYUDA DE REGISTRO</p>
                    <h2>Antes de guardar</h2>
                    <ol class="sp-guia-pasos">
                        <li><span>1</span><p>Selecciona el servicio y el mes al que corresponde el total.</p></li>
                        <li><span>2</span><p>Ingresa las unidades y litros totales del período.</p></li>
                        <li><span>3</span><p>Verifica que estos totales no estén ya desglosados en registros diarios.</p></li>
                    </ol>
                    <a class="sp-guia-enlace" href="#consulta-mensual">Consultar producción del mes <span aria-hidden="true">›</span></a>
                </section>
            </aside>
        </div>

        <section class="panel sp-card sp-seccion sp-mensual-consulta" id="consulta-mensual" aria-labelledby="sp-mensual-titulo">
            <div class="sp-listado-cabecera">
                <div>
                    <p class="sp-seccion-etiqueta">CONSULTA Y CORRECCIÓN</p>
                    <h2 id="sp-mensual-titulo">Producción del mes por servicio</h2>
                    <p>Selecciona año y mes para revisar los registros diarios y corregirlos desde cada servicio.</p>
                </div>
            </div>
            @php
                $mesesHistorial = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
                $nombreMesSeleccionado = $mesesHistorial[$mes_seleccionado] ?? '';
            @endphp
            <form class="sp-filtros-mensuales" method="GET" action="{{ route('secretaria.produccion') }}" aria-label="Elegir año y mes de producción">
                <input type="hidden" name="seccion" value="mensual">
                <div class="sp-campo">
                    <label for="sp-anio-mensual">Año</label>
                    <select id="sp-anio-mensual" name="anio" required>
                        @foreach($anios_disponibles as $anio)
                            <option value="{{ $anio }}" @selected($anio_seleccionado === $anio)>{{ $anio }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sp-campo">
                    <label for="sp-mes-mensual">Mes</label>
                    <select id="sp-mes-mensual" name="mes" required>
                        @foreach($mesesHistorial as $numeroMes => $nombreMes)
                            <option value="{{ $numeroMes }}" @selected($mes_seleccionado === $numeroMes)>{{ $nombreMes }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-dark" type="submit">Consultar</button>
            </form>

            <div class="sp-mensual-seleccion">
                <div><strong>{{ $nombreMesSeleccionado }} de {{ $anio_seleccionado }}</strong></div>
                <span class="sp-total-registros">{{ number_format($total_registros_mes, 0, ',', '.') }} registros en el mes</span>
            </div>
            <div class="reportes-tabla sp-tabla-mensual-real">
                <table>
                    <thead><tr><th>Servicio clínico</th><th>Registros</th><th>Unidades</th><th>Litros equivalentes</th><th>Acción</th></tr></thead>
                    <tbody>
                        @foreach($servicios_mes as $fila)
                            <tr>
                                <td>
                                    <strong>{{ $fila['nombre'] }}</strong>
                                    @if($fila['tiene_manual'])<small class="sp-mensual-nota-manual">También tiene un consumo mensual manual</small>@endif
                                </td>
                                <td class="sp-num-col">{{ number_format($fila['registros'], 0, ',', '.') }}</td>
                                <td class="sp-num-col">{{ number_format($fila['unidades'], 0, ',', '.') }}</td>
                                <td class="sp-num-col"><strong class="sp-litros">{{ number_format($fila['litros'], 2, ',', '.') }} L</strong></td>

                                <td>
                                    <a class="sp-accion-actualizar" href="{{ route('secretaria.produccion', ['seccion' => 'mensual', 'anio' => $anio_seleccionado, 'mes' => $mes_seleccionado, 'mensual_page' => $servicios_mes->currentPage(), 'detalle_servicio' => $fila['id']]) }}#detalle-mensual">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @php
                $paginaServicios = $servicios_mes->currentPage();
                $ultimaServicios = $servicios_mes->lastPage();
                $inicioServicios = max(1, min($paginaServicios - 2, max(1, $ultimaServicios - 5)));
                $finServicios = min($ultimaServicios, $inicioServicios + 5);
            @endphp
            <div class="sp-paginacion" aria-label="Paginación de servicios del mes">
                <nav class="sp-paginacion-enlaces" aria-label="Páginas de servicios">
                    @if($servicios_mes->onFirstPage())
                        <span class="sp-pagina sp-pagina-desactivada" aria-disabled="true">Anterior</span>
                    @else
                        <a class="sp-pagina" href="{{ $servicios_mes->previousPageUrl() }}" rel="prev">Anterior</a>
                    @endif

                    @if($inicioServicios > 1)
                        <a class="sp-pagina sp-pagina-numero" href="{{ $servicios_mes->url(1) }}" aria-label="Ir a la página 1">1</a>
                        @if($inicioServicios > 2)<span class="sp-pagina-puntos" aria-hidden="true">…</span>@endif
                    @endif

                    @for($pagina = $inicioServicios; $pagina <= $finServicios; $pagina++)
                        @if($pagina === $paginaServicios)
                            <span class="sp-pagina sp-pagina-numero sp-pagina-activa" aria-current="page">{{ $pagina }}</span>
                        @else
                            <a class="sp-pagina sp-pagina-numero" href="{{ $servicios_mes->url($pagina) }}" aria-label="Ir a la página {{ $pagina }}">{{ $pagina }}</a>
                        @endif
                    @endfor

                    @if($finServicios < $ultimaServicios)
                        @if($finServicios < $ultimaServicios - 1)<span class="sp-pagina-puntos" aria-hidden="true">…</span>@endif
                        <a class="sp-pagina sp-pagina-numero" href="{{ $servicios_mes->url($ultimaServicios) }}" aria-label="Última página, {{ $ultimaServicios }}">{{ $ultimaServicios }}</a>
                    @endif

                    @if($servicios_mes->hasMorePages())
                        <a class="sp-pagina" href="{{ $servicios_mes->nextPageUrl() }}" rel="next">Siguiente</a>
                    @else
                        <span class="sp-pagina sp-pagina-desactivada" aria-disabled="true">Siguiente</span>
                    @endif
                </nav>
                <span class="sp-paginacion-total">
                    {{ number_format($servicios_mes->firstItem() ?? 0, 0, ',', '.') }}–{{ number_format($servicios_mes->lastItem() ?? 0, 0, ',', '.') }} de {{ number_format($servicios_mes->total(), 0, ',', '.') }} servicios
                </span>
            </div>
        </section>

        @if($servicio_detalle && $detalle_produccion)
        <section class="panel sp-card sp-seccion sp-detalle-mensual" id="detalle-mensual" aria-labelledby="sp-detalle-mensual-titulo">
            <div class="sp-listado-cabecera">
                <div>
                    <p class="sp-seccion-etiqueta">DETALLE DEL SERVICIO</p>
                    <h2 id="sp-detalle-mensual-titulo">{{ $servicio_detalle->nombre }} · {{ $nombreMesSeleccionado }} {{ $anio_seleccionado }}</h2>
                    <p>{{ $detalle_produccion->total() > 0 ? 'Corrige los registros de este servicio o agrega una nueva producción.' : 'Este servicio no tiene producción diaria en el período seleccionado. Puedes registrar una nueva.' }}</p>
                </div>
                <a class="btn btn-ghost" href="{{ route('secretaria.produccion', ['seccion' => 'mensual', 'anio' => $anio_seleccionado, 'mes' => $mes_seleccionado, 'mensual_page' => $servicios_mes->currentPage()]) }}#consulta-mensual">Cerrar detalle</a>
            </div>

            @php
                // En el período vigente se propone hoy; en históricos se propone
                // el primer día del mes. La usuaria puede cambiarlo antes de guardar.
                $fechaSugerida = ($anio_seleccionado === now()->year && $mes_seleccionado === now()->month)
                    ? now()->toDateString()
                    : sprintf('%04d-%02d-01', $anio_seleccionado, $mes_seleccionado);
                $urlNuevoDiario = route('secretaria.produccion', [
                    'seccion' => 'diario',
                    'servicio_id' => $servicio_detalle->id,
                    'fecha' => $fechaSugerida,
                ]).'#sp-nuevo-titulo';
            @endphp
            <div class="sp-acciones" style="margin: 0 0 18px; justify-content: flex-start;">
                <a class="btn btn-primary" href="{{ $urlNuevoDiario }}">Agregar registro diario</a>
                @if($servicio_detalle_tiene_manual)
                    <a class="btn btn-ghost" href="{{ route('secretaria.produccion', ['seccion' => 'mensual', 'anio' => $anio_seleccionado, 'mes' => $mes_seleccionado, 'servicio_id' => $servicio_detalle->id, 'periodo' => sprintf('%04d-%02d', $anio_seleccionado, $mes_seleccionado)]) }}#registro-mensual-formulario">Editar consumo mensual manual</a>
                @endif
            </div>

            @if($editando_diario)
            <div class="sp-editar-diario" id="editar-registro-diario">
                <div class="sp-listado-cabecera">
                    <div>
                        <p class="sp-seccion-etiqueta">CORRECCIÓN DE PRODUCCIÓN</p>
                        <h3>Editar registro #{{ $editando_diario->id }}</h3>
                        <p>{{ $editando_diario->fecha?->format('d/m/Y') }} · {{ $editando_diario->servicio?->nombre ?? '—' }} · {{ $editando_diario->material?->nombre ?? '—' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('secretaria.produccion.guardar') }}" class="sp-form">
                    @csrf
                    <input type="hidden" name="registro_diario_id" value="{{ $editando_diario->id }}">
                    <div class="sp-form-grid">
                        <div class="sp-campo"><label for="cantidad_editar">Cantidad (unidades) <span>*</span></label><input id="cantidad_editar" type="number" name="cantidad" min="1" step="1" value="{{ old('cantidad', $editando_diario->cantidad) }}" required></div>
                        <div class="sp-campo"><label for="factor_editar">Factor (L por unidad) <span>*</span></label><input id="factor_editar" type="number" name="factor_litros" min="0" max="99999999.99" step="0.01" value="{{ old('factor_litros', $editando_diario->factor_litros) }}" required></div>
                        <div class="sp-campo sp-ancho"><label for="observacion_editar">Observación (opcional)</label><textarea id="observacion_editar" name="observacion" rows="3" maxlength="255">{{ old('observacion', $editando_diario->observacion) }}</textarea></div>
                    </div>
                    <p class="sp-ayuda">La fecha, el servicio y el material originales se conservan. Si el factor aún no está validado, puedes dejarlo en 0; no se inventarán litros.</p>
                    <div class="sp-acciones"><a class="btn btn-ghost" href="{{ route('secretaria.produccion', ['seccion' => 'mensual', 'anio' => $anio_seleccionado, 'mes' => $mes_seleccionado, 'mensual_page' => $servicios_mes->currentPage(), 'detalle_servicio' => $servicio_detalle->id]) }}#detalle-mensual">Cancelar</a><button type="submit" class="btn btn-primary">Guardar corrección</button></div>
                </form>
            </div>
            @endif

            <div class="reportes-tabla sp-tabla-registros">
                <table>
                    <thead><tr><th>Fecha</th><th>Material</th><th>Cantidad</th><th>Factor L/u</th><th>Litros</th><th>Observación</th><th>Acción</th></tr></thead>
                    <tbody>
                        @forelse($detalle_produccion as $fila)
                            <tr>
                                <td class="sp-fecha">{{ $fila->fecha?->format('d/m/Y') }}</td>
                                <td class="sp-material"><strong>{{ $fila->material?->nombre ?? '—' }}</strong></td>
                                <td class="sp-num-col">{{ number_format($fila->cantidad, 0, ',', '.') }}</td>
                                <td class="sp-num-col">{{ number_format((float) $fila->factor_litros, 2, ',', '.') }}</td>
                                <td class="sp-num-col"><strong class="sp-litros">{{ number_format((float) $fila->litros, 2, ',', '.') }} L</strong></td>
                                <td class="sp-observacion">{{ $fila->observacion ?: '—' }}</td>
                                <td><a class="sp-accion-actualizar" href="{{ route('secretaria.produccion', ['seccion' => 'mensual', 'anio' => $anio_seleccionado, 'mes' => $mes_seleccionado, 'detalle_servicio' => $servicio_detalle->id, 'detalle_page' => $detalle_produccion->currentPage(), 'editar_registro' => $fila->id]) }}#editar-registro-diario">Editar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="reportes-vacio">Sin registros diarios en este mes. Usa «Agregar registro diario» para comenzar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @php
                $paginaDetalle = $detalle_produccion->currentPage();
                $ultimaDetalle = $detalle_produccion->lastPage();
                $inicioDetalle = max(1, min($paginaDetalle - 2, max(1, $ultimaDetalle - 5)));
                $finDetalle = min($ultimaDetalle, $inicioDetalle + 5);
            @endphp
            <div class="sp-paginacion">
                <nav class="sp-paginacion-enlaces" aria-label="Páginas del detalle mensual">
                    @if($detalle_produccion->onFirstPage())
                        <span class="sp-pagina sp-pagina-desactivada" aria-disabled="true">Anterior</span>
                    @else
                        <a class="sp-pagina" href="{{ $detalle_produccion->previousPageUrl() }}">Anterior</a>
                    @endif
                    @if($inicioDetalle > 1)
                        <a class="sp-pagina sp-pagina-numero" href="{{ $detalle_produccion->url(1) }}">1</a>
                        @if($inicioDetalle > 2)<span class="sp-pagina-puntos">…</span>@endif
                    @endif
                    @for($numero = $inicioDetalle; $numero <= $finDetalle; $numero++)
                        @if($numero === $paginaDetalle)
                            <span class="sp-pagina sp-pagina-numero sp-pagina-activa" aria-current="page">{{ $numero }}</span>
                        @else
                            <a class="sp-pagina sp-pagina-numero" href="{{ $detalle_produccion->url($numero) }}">{{ $numero }}</a>
                        @endif
                    @endfor
                    @if($finDetalle < $ultimaDetalle)
                        @if($finDetalle < $ultimaDetalle - 1)<span class="sp-pagina-puntos">…</span>@endif
                        <a class="sp-pagina sp-pagina-numero" href="{{ $detalle_produccion->url($ultimaDetalle) }}">{{ $ultimaDetalle }}</a>
                    @endif
                    @if($detalle_produccion->hasMorePages())
                        <a class="sp-pagina" href="{{ $detalle_produccion->nextPageUrl() }}">Siguiente</a>
                    @else
                        <span class="sp-pagina sp-pagina-desactivada" aria-disabled="true">Siguiente</span>
                    @endif
                </nav>
                <span class="sp-paginacion-total">{{ number_format($detalle_produccion->firstItem() ?? 0, 0, ',', '.') }}–{{ number_format($detalle_produccion->lastItem() ?? 0, 0, ',', '.') }} de {{ number_format($detalle_produccion->total(), 0, ',', '.') }}</span>
            </div>
        </section>
        @endif

        @elseif($pestanaProduccion === 'registros')
        <section class="panel sp-card sp-seccion sp-listado" id="produccion-registros" aria-labelledby="sp-listado-titulo">
            <div class="sp-listado-cabecera">
                <div>
                    <p class="sp-seccion-etiqueta">HISTORIAL DIARIO</p>
                    <h2 id="sp-listado-titulo">Producción diaria registrada</h2>
                    <p>Consulta los registros almacenados, ordenados desde la fecha más reciente.</p>
                </div>
                <span class="sp-total-registros">{{ number_format($registros_produccion->total(), 0, ',', '.') }} registros en total</span>
            </div>

            <div class="reportes-tabla sp-tabla-registros">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Servicio</th>
                            <th>Material</th>
                            <th>Cantidad</th>
                            <th>Factor L/u</th>
                            <th>Litros</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros_produccion as $fila)
                            <tr>
                                <td class="sp-fecha">{{ $fila->fecha?->format('d/m/Y') }}</td>
                                <td class="sp-servicio">{{ $fila->servicio?->nombre ?? '—' }}</td>
                                <td class="sp-material"><strong>{{ $fila->material?->nombre ?? '—' }}</strong></td>
                                <td class="sp-num-col">{{ number_format($fila->cantidad, 0, ',', '.') }}</td>
                                <td class="sp-num-col">{{ number_format((float) $fila->factor_litros, 2, ',', '.') }}</td>
                                <td class="sp-num-col"><strong class="sp-litros">{{ number_format((float) $fila->litros, 2, ',', '.') }} L</strong></td>
                                <td class="sp-observacion" title="{{ $fila->observacion ?? '' }}">{{ $fila->observacion ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="reportes-vacio">Todavía no hay registros de producción diaria.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @php
                // Asegurar que los enlaces de paginación siempre abran la pestaña del listado.
                $registros_produccion->appends(['seccion' => 'registros']);
                $paginaActual = $registros_produccion->currentPage();
                $ultimaPagina = $registros_produccion->lastPage();
                $inicioPaginas = max(1, min($paginaActual - 2, max(1, $ultimaPagina - 5)));
                $finPaginas = min($ultimaPagina, $inicioPaginas + 5);
            @endphp
            <div class="sp-paginacion" aria-label="Paginación de producción diaria">
                <nav class="sp-paginacion-enlaces" aria-label="Páginas de producción">
                    @if($registros_produccion->onFirstPage())
                        <span class="sp-pagina sp-pagina-desactivada" aria-disabled="true">Anterior</span>
                    @else
                        <a class="sp-pagina sp-pagina-anterior" href="{{ $registros_produccion->previousPageUrl() }}" rel="prev">Anterior</a>
                    @endif

                    @if($inicioPaginas > 1)
                        <a class="sp-pagina sp-pagina-numero" href="{{ $registros_produccion->url(1) }}" aria-label="Ir a la página 1">1</a>
                        @if($inicioPaginas > 2)<span class="sp-pagina-puntos" aria-hidden="true">…</span>@endif
                    @endif

                    @for($pagina = $inicioPaginas; $pagina <= $finPaginas; $pagina++)
                        @if($pagina === $paginaActual)
                            <span class="sp-pagina sp-pagina-numero sp-pagina-activa" aria-current="page">{{ $pagina }}</span>
                        @else
                            <a class="sp-pagina sp-pagina-numero" href="{{ $registros_produccion->url($pagina) }}" aria-label="Ir a la página {{ $pagina }}">{{ $pagina }}</a>
                        @endif
                    @endfor

                    @if($finPaginas < $ultimaPagina)
                        @if($finPaginas < $ultimaPagina - 1)<span class="sp-pagina-puntos" aria-hidden="true">…</span>@endif
                        <a class="sp-pagina sp-pagina-numero" href="{{ $registros_produccion->url($ultimaPagina) }}" aria-label="Ir a la última página, {{ $ultimaPagina }}">{{ $ultimaPagina }}</a>
                    @endif

                    @if($registros_produccion->hasMorePages())
                        <a class="sp-pagina sp-pagina-siguiente" href="{{ $registros_produccion->nextPageUrl() }}" rel="next">Siguiente</a>
                    @else
                        <span class="sp-pagina sp-pagina-desactivada" aria-disabled="true">Siguiente</span>
                    @endif
                </nav>

                <span class="sp-paginacion-total">
                    {{ number_format($registros_produccion->firstItem() ?? 0, 0, ',', '.') }}–{{ number_format($registros_produccion->lastItem() ?? 0, 0, ',', '.') }} de {{ number_format($registros_produccion->total(), 0, ',', '.') }}
                </span>
            </div>
        </section>

        @endif
    </main>
</div>
@endsection
