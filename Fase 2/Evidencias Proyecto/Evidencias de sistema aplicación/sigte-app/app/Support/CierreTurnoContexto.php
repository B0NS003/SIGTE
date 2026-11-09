<?php

namespace App\Support;

use App\Models\Caja;
use App\Models\CierreTurno;
use App\Models\ElementoInventario;
use App\Models\Entrega;
use App\Models\Personal;
use App\Models\Rol;
use App\Models\User;
use Carbon\Carbon;

class CierreTurnoContexto
{
    /**
     * @return array{
     *   momento: Carbon,
     *   turno_clave: string,
     *   turno_meta: array{etiqueta: string, horario: string},
     *   ventana: array{inicio: Carbon, fin: Carbon},
     *   turno_fecha: string,
     *   ya_cerrado: ?CierreTurno,
     *   puede_cerrar: bool,
     *   motivo_bloqueo: ?string,
     *   personal_responsable: ?Personal,
     *   turno: array{fecha: string, bloque: string, responsable: string, relevo: string, operadoras: string},
     *   relevos: list<array{id: int, nombre: string}>,
     *   resumen: list<array{label: string, value: int, alerta?: bool}>,
     *   alertas: list<array{codigo: string, detalle: string, nivel: string}>,
     *   pendientes: list<string>,
     *   historial: list<array{fecha: string, bloque: string, responsable: string, estado: string}>
     * }
     */
    public static function para(?User $usuario): array
    {
        $momento = now()->timezone('America/Santiago');
        $turnoClave = Personal::turnoVigente($momento);
        $turnoMeta = Personal::TURNOS[$turnoClave];
        $ventana = self::ventanaTurno($momento, $turnoClave);
        $turnoFecha = $ventana['inicio']->toDateString();

        $personalUsuario = $usuario
            ? Personal::query()->where('user_id', $usuario->id)->where('activo', true)->first()
            : null;

        $delBloque = Personal::query()
            ->where('activo', true)
            ->where('turno', $turnoClave)
            ->orderBy('nombre')
            ->get();

        $operadoras = $delBloque
            ->pluck('nombre')
            ->map(fn (string $n) => self::iniciales($n))
            ->filter()
            ->values();

        $yaCerrado = CierreTurno::query()
            ->with('responsable:id,name')
            ->whereDate('turno_fecha', $turnoFecha)
            ->where('turno_clave', $turnoClave)
            ->first();

        $puedeCerrar = true;
        $motivo = null;
        if ($yaCerrado !== null) {
            $puedeCerrar = false;
            $motivo = 'Este bloque ya tiene un cierre registrado.';
        } elseif ($usuario === null || ! $usuario->isEnfermera()) {
            $puedeCerrar = false;
            $motivo = 'Solo la enfermera de turno puede registrar el cierre.';
        } elseif ($personalUsuario !== null && $personalUsuario->turno !== $turnoClave) {
            $puedeCerrar = false;
            $motivo = 'Tu asignación de turno no corresponde al bloque vigente ('.
                $turnoMeta['etiqueta'].').';
        }

        $siguienteClave = self::siguienteTurno($turnoClave);
        $sugerido = Personal::query()
            ->where('activo', true)
            ->where('turno', $siguienteClave)
            ->orderBy('nombre')
            ->first();

        $relevos = User::query()
            ->where('activo', true)
            ->whereHas('rol', fn ($q) => $q->where('nombre', Rol::ENFERMERA))
            ->when($usuario, fn ($q) => $q->whereKeyNot($usuario->id))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $u) => ['id' => $u->id, 'nombre' => $u->name])
            ->values()
            ->all();

        $alertas = self::alertasAbiertas();
        $pendientes = self::pendientesDesde($alertas);
        $resumen = self::resumen($ventana, count($alertas));

        $historial = CierreTurno::query()
            ->with('responsable:id,name')
            ->orderByDesc('cerrado_at')
            ->limit(8)
            ->get()
            ->map(fn (CierreTurno $c) => [
                'fecha' => $c->cerrado_at?->timezone('America/Santiago')->format('d M') ?? '—',
                'bloque' => $c->turno_etiqueta,
                'responsable' => $c->responsable?->name ?? '—',
                'estado' => 'Cerrado',
            ])
            ->all();

        return [
            'momento' => $momento,
            'turno_clave' => $turnoClave,
            'turno_meta' => $turnoMeta,
            'ventana' => $ventana,
            'turno_fecha' => $turnoFecha,
            'ya_cerrado' => $yaCerrado,
            'puede_cerrar' => $puedeCerrar,
            'motivo_bloqueo' => $motivo,
            'personal_responsable' => $personalUsuario,
            'turno' => [
                'fecha' => $momento->format('d/m/Y'),
                'bloque' => $turnoMeta['etiqueta'].' · '.str_replace(' a ', '–', $turnoMeta['horario']),
                'responsable' => $usuario?->name ?? 'Sin sesión',
                'relevo' => $sugerido?->nombre
                    ?? ($relevos[0]['nombre'] ?? 'Sin relevo asignado'),
                'operadoras' => $operadoras->isEmpty()
                    ? 'Sin operadoras en el bloque'
                    : $operadoras->implode(', '),
            ],
            'relevos' => $relevos,
            'resumen' => $resumen,
            'alertas' => $alertas,
            'pendientes' => $pendientes,
            'historial' => $historial,
        ];
    }

    /** @return array{inicio: Carbon, fin: Carbon} */
    public static function ventanaTurno(Carbon $momento, string $turnoClave): array
    {
        $base = $momento->copy()->timezone('America/Santiago');
        if ($turnoClave === 'manana') {
            return [
                'inicio' => $base->copy()->setTime(8, 0),
                'fin' => $base->copy()->setTime(16, 0),
            ];
        }
        if ($turnoClave === 'tarde') {
            return [
                'inicio' => $base->copy()->setTime(16, 0),
                'fin' => $base->copy()->addDay()->startOfDay(),
            ];
        }

        // Noche 00:00–08:00: si aún no son las 8, el bloque empezó a las 00:00 de hoy.
        return [
            'inicio' => $base->copy()->startOfDay(),
            'fin' => $base->copy()->setTime(8, 0),
        ];
    }

    public static function siguienteTurno(string $turnoClave): string
    {
        return match ($turnoClave) {
            'manana' => 'tarde',
            'tarde' => 'noche',
            default => 'manana',
        };
    }

    /**
     * @return list<array{label: string, value: int, alerta?: bool}>
     */
    private static function resumen(array $ventana, int $alertas): array
    {
        $inicio = $ventana['inicio'];
        $fin = $ventana['fin'];

        $recepciones = Caja::query()
            ->whereBetween('created_at', [$inicio->clone()->utc(), $fin->clone()->utc()])
            ->count();

        $entregas = 0;
        if (class_exists(Entrega::class)) {
            $entregas = Entrega::query()
                ->whereBetween('entregado_at', [$inicio->clone()->utc(), $fin->clone()->utc()])
                ->count();
        }

        $enProceso = Caja::query()->whereNull('entregado_at')->count();

        return [
            ['label' => 'Recepciones', 'value' => $recepciones],
            ['label' => 'Entregas', 'value' => $entregas],
            ['label' => 'En proceso al cierre', 'value' => $enProceso],
            ['label' => 'Alertas abiertas', 'value' => $alertas, 'alerta' => $alertas > 0],
        ];
    }

    /**
     * @return list<array{codigo: string, detalle: string, nivel: string}>
     */
    private static function alertasAbiertas(): array
    {
        $cajas = Caja::query()
            ->with('servicioClinico:id,nombre')
            ->whereNull('entregado_at')
            ->where('urgente', true)
            ->orderBy('codigo')
            ->limit(20)
            ->get()
            ->map(fn (Caja $caja) => [
                'codigo' => $caja->codigo,
                'detalle' => trim(
                    ($caja->servicio !== '' ? $caja->servicio.' · ' : '').
                    'Marcada urgente en '.Caja::etiquetaEtapa((string) $caja->etapa).
                    ($caja->ubicacion ? ' · '.$caja->ubicacion : '')
                ),
                'nivel' => 'Urgente',
            ]);

        $stock = ElementoInventario::query()
            ->with('salaFisica:id,nombre,codigo')
            ->orderBy('codigo')
            ->get()
            ->filter(fn (ElementoInventario $e) => $e->tieneAlertaStock())
            ->take(15)
            ->map(function (ElementoInventario $e) {
                $estado = $e->estadoStock();

                return [
                    'codigo' => $e->codigo,
                    'detalle' => trim(
                        ($e->nombre ?? $e->codigo).
                        ' · stock '.$e->stock.' / mínimo '.$e->minimo.
                        ($e->salaFisica?->nombre ? ' · '.$e->salaFisica->nombre : '')
                    ),
                    'nivel' => $estado === 'critico' ? 'Crítico' : 'Stock bajo',
                ];
            });

        return $cajas->concat($stock)->values()->all();
    }

    /**
     * @param  list<array{codigo: string, detalle: string, nivel: string}>  $alertas
     * @return list<string>
     */
    private static function pendientesDesde(array $alertas): array
    {
        return collect($alertas)
            ->map(fn (array $a) => $a['codigo'].' — '.$a['detalle'])
            ->values()
            ->all();
    }

    private static function iniciales(string $nombre): string
    {
        $partes = preg_split('/\s+/', trim($nombre)) ?: [];
        if ($partes === []) {
            return $nombre;
        }
        if (count($partes) === 1) {
            return $partes[0];
        }

        return mb_substr($partes[0], 0, 1).'. '.$partes[count($partes) - 1];
    }
}
