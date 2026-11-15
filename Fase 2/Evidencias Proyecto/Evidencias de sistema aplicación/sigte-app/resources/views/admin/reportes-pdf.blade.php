<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>SIGTE — Reporte de {{ ucfirst($tab) }}</title>
    <style>
        @page {
            margin: 28px 34px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #17263a;
            font-size: 9px;
        }

        h1 {
            font-size: 17px;
            color: #143b5c;
            margin: 0 0 6px;
        }

        h2 {
            font-size: 12px;
            color: #143b5c;
            margin: 17px 0 6px;
        }

        p {
            margin: 3px 0;
        }

        .meta {
            color: #576579;
            margin-bottom: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 12px;
        }

        th,
        td {
            border: 1px solid #cfdae7;
            padding: 5px 7px;
            vertical-align: top;
        }

        th {
            background: #e6edf5;
            text-align: left;
        }

        tr {
            page-break-inside: avoid;
        }

        .numero {
            text-align: right;
            white-space: nowrap;
        }

        .resumen {
            width: 58%;
        }

        .nota {
            font-size: 8px;
            color: #66788b;
        }
    </style>
</head>

<body>
    <h1>SIGTE — Reporte de {{ ucfirst($tab) }}</h1>
    <p class="meta">Hospital San José de Melipilla · Período consultado: {{ $filtros['desde'] }} al {{ $filtros['hasta'] }}</p>
    <p class="meta"><strong>Generado por:</strong> {{ auth()->user()?->name ?? 'Usuario no identificado' }} · <strong>Fecha y hora:</strong> {{ $fecha_generacion }} (Chile)</p>
    <h2>Resumen</h2>
    <table class="resumen">
        <thead>
            <tr>
                <th>Indicador</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resumen as $fila)
            <tr>
                <td>{{ $fila[0] }}</td>
                <td class="numero">{{ is_float($fila[1]) ? number_format($fila[1], 2, ',', '.') : number_format($fila[1], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if ($tab === 'produccion')
    <h2>Distribución de cajas en el flujo (estado actual)</h2>
    <p class="nota">Las cifras del flujo son actuales y no dependen de la fecha elegida. El filtro de servicio, si se seleccionó, sí aplica.</p>
    <table>
        <thead>
            <tr>
                <th>Etapa</th>
                <th>Cajas actuales</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['en_flujo'] as $fila)
            <tr>
                <td>{{ $fila['etapa'] }}</td>
                <td class="numero">{{ $fila['cantidad'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2">No hay cajas actualmente en flujo.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @php
        $historicoPdf = collect($datos['cajas_por_etapa'] ?? []);
        $totalHistoricoPdf = (int) $historicoPdf->sum('cantidad');
    @endphp
    <h2>Procesamiento histórico por etapa</h2>
    <p class="nota">Ciclos (no códigos físicos) que pasaron por cada etapa en el período. El filtro de servicio, si se seleccionó, sí aplica.</p>
    <table>
        <thead>
            <tr>
                <th>Etapa</th>
                <th>Ciclos</th>
                <th>% del total contabilizado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($historicoPdf as $fila)
            <tr>
                <td>{{ $fila['etapa'] }}</td>
                <td class="numero">{{ $fila['cantidad'] }}</td>
                <td class="numero">{{ $totalHistoricoPdf > 0 ? number_format($fila['cantidad'] / $totalHistoricoPdf * 100, 1, ',', '.') : '0' }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="3">No hay movimiento histórico en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <h2>Producción mensual por servicio</h2>
    <table>
        <thead>
            <tr>
                <th>Mes</th>
                <th>Servicio</th>
                <th>Unidades</th>
                <th>Litros</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['produccion']['detalle'] as $fila)
            <tr>
                <td>{{ $fila->mes }}</td>
                <td>{{ $fila->servicio }}</td>
                <td class="numero">{{ number_format($fila->cantidad, 0, ',', '.') }}</td>
                <td class="numero">{{ number_format($fila->litros, 2, ',', '.') }} L</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No hay producción registrada en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @elseif ($tab === 'trazabilidad')
    <h2>Movimientos por tipo</h2>
    <table class="resumen">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['por_tipo'] as $tipo => $cantidad)
            <tr>
                <td>{{ \App\Models\RegistroAuditoria::TIPOS[$tipo] ?? $tipo }}</td>
                <td class="numero">{{ $cantidad }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2">No hay movimientos en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <h2>Historial de movimientos</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Tipo</th>
                <th>Objeto</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['movimientos'] as $fila)
            <tr>
                <td>{{ $fila['fecha'] }}</td>
                <td>{{ $fila['hora'] }}</td>
                <td>{{ $fila['actor'] }}</td>
                <td>{{ $fila['accion'] }}</td>
                <td>{{ $fila['tipo_etiqueta'] }}</td>
                <td>{{ $fila['objeto'] }}</td>
                <td>{{ $fila['detalle'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7">No hay movimientos de auditoría.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @else
    <h2>Cajas entregadas por servicio de destino</h2>
    <table class="resumen">
        <thead>
            <tr>
                <th>Servicio</th>
                <th>Cajas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['entregas_por_servicio'] as $nombre => $cantidad)
            <tr>
                <td>{{ $nombre }}</td>
                <td class="numero">{{ $cantidad }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2">No hay cajas entregadas en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <p class="nota">Una operación puede incluir varias cajas o solamente material. Por eso las operaciones y las cajas entregadas se cuentan por separado.</p>
    <h2>Detalle de cajas entregadas</h2>
    <table>
        <thead>
            <tr>
                <th>Caja</th>
                <th>Operación</th>
                <th>Servicio destino</th>
                <th>Fecha</th>
                <th>Responsable</th>
                <th>Recibe</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datos['entregas'] as $fila)
            <tr>
                <td>{{ $fila['codigo'] }}</td>
                <td>{{ $fila['operacion'] }}</td>
                <td>{{ $fila['servicio'] }}</td>
                <td>{{ $fila['cuando'] }}</td>
                <td>{{ $fila['responsable'] }}</td>
                <td>{{ $fila['recibe'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6">No hay cajas entregadas en el período.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif
</body>

</html>
