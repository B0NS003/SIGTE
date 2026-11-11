<?php

namespace App\Support;

use App\Models\Caja;
use App\Models\RegistroAuditoria;
use App\Models\User;

/**
 * Datos del panel de supervisión. Reutiliza la misma ventana y alertas
 * que el cierre de turno, sin modificar los registros ni el cierre.
 */
class ResumenTurnoEnfermera
{
    /** @param list<array<string, mixed>> $cajas */
    public static function construir(?User $usuario, array $cajas): array
    {
        $ctx = CierreTurnoContexto::para($usuario);
        $fases = Caja::etapas();
        $limites = [
            Caja::ETAPA_RECEPCION => 60,
            Caja::ETAPA_LAVADO => 60,
            Caja::ETAPA_PREPARACION => 90,
            Caja::ETAPA_ESTERILIZACION => 45,
            Caja::ETAPA_ALMACEN => 240,
            Caja::ETAPA_ENTREGA => 60,
        ];
        $cuentas = array_fill(0, count($fases), 0);
        $demoradas = [];
        $criticas = 0;
        $urgentes = 0;

        foreach ($cajas as $caja) {
            $indice = (int) ($caja['fase_idx'] ?? 0);
            if (! array_key_exists($indice, $cuentas)) {
                continue;
            }

            $cuentas[$indice]++;
            $etapa = $fases[$indice];
            $minutos = $caja['minutos'] ?? null;
            $esUrgente = (bool) ($caja['urgente'] ?? false);
            $esDemorada = $minutos !== null && (int) $minutos > $limites[$etapa];

            if ($esUrgente || $esDemorada) {
                $criticas++;
            }
            if ($esUrgente) {
                $urgentes++;
            }
            if ($esDemorada) {
                $demoradas[] = [
                    'codigo' => (string) ($caja['id'] ?? 'Sin código'),
                    'detalle' => 'Lleva '.(string) ($caja['tiempo'] ?? $minutos.' min').' en '.Caja::etiquetaEtapa($etapa).
                        ((string) ($caja['servicio'] ?? '') !== '' ? ' · '.$caja['servicio'] : ''),
                    'nivel' => $esUrgente ? 'Urgente' : 'Demora',
                    'prioridad' => $esUrgente ? 0 : 2,
                    'minutos' => (int) $minutos,
                ];
            }
        }

        $total = count($cajas);
        $flujo = [];
        foreach ($fases as $i => $etapa) {
            $cantidad = $cuentas[$i];
            $flujo[] = [
                'nombre' => Caja::etiquetaEtapa($etapa),
                'cantidad' => $cantidad,
                // Porcentaje del total: nunca un progreso ficticio del proceso.
                'porcentaje' => $total > 0 ? round($cantidad / $total * 100, 1) : 0,
            ];
        }

        $atencion = [];
        $codigosAgregados = [];
        foreach ($ctx['alertas'] as $alerta) {
            $codigo = (string) $alerta['codigo'];
            $nivel = (string) $alerta['nivel'];
            $atencion[] = [
                'codigo' => $codigo,
                'detalle' => (string) $alerta['detalle'],
                'nivel' => $nivel,
                'prioridad' => in_array($nivel, ['Crítico', 'Urgente'], true) ? 0 : 1,
                'minutos' => 0,
            ];
            $codigosAgregados[$codigo] = true;
        }
        foreach ($demoradas as $alerta) {
            if (! isset($codigosAgregados[$alerta['codigo']])) {
                $atencion[] = $alerta;
            }
        }
        usort($atencion, fn (array $a, array $b) =>
            ($a['prioridad'] <=> $b['prioridad']) ?: ($b['minutos'] <=> $a['minutos']));

        $inicio = $ctx['ventana']['inicio']->copy()->utc();
        $fin = $ctx['ventana']['fin']->copy()->utc();
        $actividad = RegistroAuditoria::query()
            ->with('actor:id,name')
            ->whereIn('tipo', ['etapa', 'entrega'])
            ->whereBetween('created_at', [$inicio, $fin])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (RegistroAuditoria $registro) => [
                'hora' => $registro->created_at?->copy()->timezone('America/Santiago')->format('H:i') ?? '—',
                'accion' => (string) $registro->accion,
                'objeto' => (string) ($registro->objeto ?? ''),
                'detalle' => (string) ($registro->detalle ?? ''),
                'actor' => $registro->actor?->name ?? 'Sin identificar',
            ])->all();

        // El cierre usa operaciones de entrega (no cantidad de cajas),
        // por ello conservamos esa unidad en el indicador.
        $entregas = collect($ctx['resumen'])
            ->firstWhere('label', 'Entregas')['value'] ?? 0;

        return [
            'turno' => $ctx['turno'],
            'turno_clave' => $ctx['turno_clave'],
            'turno_fecha' => $ctx['turno_fecha'],
            'puede_cerrar' => $ctx['puede_cerrar'],
            'ya_cerrado' => $ctx['ya_cerrado'] !== null,
            'motivo_bloqueo' => $ctx['motivo_bloqueo'],
            'total_cajas' => $total,
            'alertas_total' => count($ctx['alertas']),
            'criticas_total' => $criticas,
            'urgentes_total' => $urgentes,
            'pendientes_total' => count($ctx['pendientes']),
            'entregas_turno' => (int) $entregas,
            'atencion' => array_slice($atencion, 0, 4),
            'atencion_total' => count($atencion),
            'flujo' => $flujo,
            'actividad' => $actividad,
            'momento' => $ctx['momento']->format('d/m/Y H:i'),
        ];
    }
}
