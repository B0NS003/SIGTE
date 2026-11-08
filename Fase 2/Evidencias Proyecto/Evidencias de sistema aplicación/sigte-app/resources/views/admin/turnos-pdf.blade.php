<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>SIGTE — Turnos {{ $fechaAsignacion ?? $fecha }}</title>
    <style>
        @page { margin: 24px 28px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a2332;
            line-height: 1.35;
        }
        .cabecera {
            border-bottom: 2px solid #1a5f7a;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .marca {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.04em;
            color: #1a5f7a;
        }
        .marca span { color: #0d3b4c; }
        .titulo {
            font-size: 15px;
            margin: 6px 0 2px;
            font-weight: bold;
        }
        .meta {
            color: #4a5568;
            font-size: 10px;
            margin-top: 2px;
        }
        .resumen {
            margin: 0 0 14px;
            padding: 8px 10px;
            background: #f0f6f8;
            border: 1px solid #d5e4ea;
        }
        .bloque {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .bloque-head {
            background: #1a5f7a;
            color: #fff;
            padding: 6px 10px;
        }
        .bloque-head.libre { background: #5a6a78; }
        .bloque-head.ahora { background: #0d7a4f; }
        .bloque-head strong { font-size: 12px; }
        .bloque-head span {
            float: right;
            font-size: 10px;
            opacity: 0.92;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #c9d5dc;
            padding: 5px 8px;
            text-align: left;
        }
        th {
            background: #eef4f7;
            font-size: 10px;
            color: #3d4f5c;
            font-weight: bold;
        }
        td.num {
            width: 36px;
            text-align: center;
            color: #5a6a78;
        }
        td.fecha {
            width: 110px;
            white-space: nowrap;
            color: #3d4f5c;
        }
        .vacio {
            padding: 10px;
            color: #6b7c88;
            font-style: italic;
            border: 1px solid #c9d5dc;
            border-top: 0;
        }
        .pie {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #c9d5dc;
            font-size: 9px;
            color: #6b7c88;
        }
    </style>
</head>
<body>
    <div class="cabecera">
        <div class="marca">SIG<span>TE</span></div>
        <div class="titulo">Asignación de turnos del puesto</div>
        <div class="meta">
            @if ($fechaAsignacion)
                Fecha de la asignación: <strong>{{ $fechaAsignacion }}{{ $horaAsignacion ? ' '.$horaAsignacion : '' }}</strong>
            @else
                Fecha de la asignación: <strong>Sin asignaciones con fecha registrada</strong>
            @endif
        </div>
        <div class="meta">PDF generado el {{ $fecha }} a las {{ $hora }} · Zona horaria America/Santiago</div>
    </div>

    <div class="resumen">
        Personal en nómina: <strong>{{ $total }}</strong>
        · Turno vigente ahora:
        <strong>
            @if ($turnoVigente === 'manana') Mañana
            @elseif ($turnoVigente === 'tarde') Tarde
            @else Noche
            @endif
        </strong>
    </div>

    @foreach ($porTurno as $clave => $bloque)
        @php
            $clase = match ($clave) {
                'manana', 'tarde', 'noche' => $clave === $turnoVigente ? 'ahora' : '',
                default => 'libre',
            };
        @endphp
        <section class="bloque">
            <div class="bloque-head {{ $clase }}">
                <strong>{{ $bloque['etiqueta'] }}</strong>
                <span>{{ $bloque['horario'] }} · {{ $bloque['total'] }} {{ $bloque['total'] === 1 ? 'persona' : 'personas' }}</span>
            </div>
            @if ($bloque['personas']->isEmpty())
                <div class="vacio">Sin personal asignado.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th class="num">#</th>
                            <th>Nombre</th>
                            @if ($clave !== '')
                                <th class="fecha">Asignado el</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bloque['personas'] as $i => $persona)
                            <tr>
                                <td class="num">{{ $i + 1 }}</td>
                                <td>{{ $persona['nombre'] }}</td>
                                @if ($clave !== '')
                                    <td class="fecha">{{ $persona['asignado_el'] ?? '—' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    @endforeach

    <div class="pie">
        Documento generado por SIGTE · Uso interno del servicio · No altera la asignación guardada en el sistema.
    </div>
</body>
</html>
