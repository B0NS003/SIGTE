<?php

namespace App\Services\Flujo;

use App\Models\Caja;
use App\Support\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class RetrocesoEtapa
{
    /**
     * HIU-EP2-003: un paso atrás, con motivo, solo desde lavado o preparación.
     *
     * @param  array{user_id: int|null, personal_id: int|null}  $marca
     * @return array{caja: Caja, desde: string, hacia: string, motivo: string}
     */
    public function ejecutar(string $codigo, string $motivo, array $marca): array
    {
        $caja = Caja::query()->where('codigo', $codigo)->first();
        if ($caja === null) {
            throw ValidationException::withMessages([
                'caja' => 'Esa caja no está en el flujo.',
            ]);
        }

        $anterior = $caja->etapaAnteriorPermitida();
        $etiquetaActual = Caja::etiquetaEtapa(trim((string) $caja->etapa));

        if ($anterior === null) {
            throw ValidationException::withMessages([
                'etapa' => 'El retroceso no está permitido. '.$caja->codigo.' sigue en '.$etiquetaActual.'.',
            ]);
        }

        $motivo = mb_substr($motivo, 0, 180);
        $etiquetaAnterior = Caja::etiquetaEtapa($anterior);

        try {
            DB::transaction(function () use ($caja, $anterior, $motivo, $marca): void {
                $origen = trim((string) $caja->etapa);
                $caja->etapa = $anterior;
                $caja->estado = 'En '.Caja::etiquetaEtapa($anterior);
                $caja->actividad = null;
                $caja->etapa_desde = now();
                $caja->proceso_desde = null;
                $caja->proceso_hasta = null;
                $caja->responsable_id = $marca['user_id'];
                $caja->save();
                $caja->retrocesos()->create([
                    'user_id' => $marca['user_id'],
                    'etapa_desde' => $origen,
                    'etapa_hacia' => $anterior,
                    'motivo' => $motivo,
                ]);
                $caja->anotaciones()->create([
                    'user_id' => $marca['user_id'],
                    'texto' => 'Volvió a '.Caja::etiquetaEtapa($anterior).'. '.$motivo,
                ]);
            });
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'etapa' => 'No fue posible completar el retroceso. '.$caja->codigo.' sigue en '.$etiquetaActual.'.',
            ]);
        }

        Auditoria::registrar(
            'Corrigió etapa',
            'etapa',
            $caja->codigo,
            $etiquetaActual.' → '.$etiquetaAnterior.'. '.$motivo,
        );

        return [
            'caja' => $caja,
            'desde' => $etiquetaActual,
            'hacia' => $etiquetaAnterior,
            'motivo' => $motivo,
        ];
    }
}
