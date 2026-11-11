<?php

namespace App\Services\Flujo;

use App\Models\Caja;
use App\Support\Auditoria;
use Illuminate\Validation\ValidationException;

class AvanceEtapa
{
    /**
     * HIU-EP2-002: avanza una sola etapa y deja fecha, hora y usuario.
     *
     * @param  array{user_id: int|null, personal_id: int|null}  $marca
     * @return array{caja: Caja, desde: string, hacia: string}
     */
    public function ejecutar(string $codigo, string $destino, array $marca): array
    {
        $caja = Caja::query()->where('codigo', $codigo)->first();
        if ($caja === null) {
            throw ValidationException::withMessages([
                'caja' => 'Esa caja no está en el flujo.',
            ]);
        }

        $etapas = Caja::etapas();
        $indice = $caja->indiceEtapa();
        $siguiente = $etapas[$indice + 1] ?? null;
        $etiquetaActual = Caja::etiquetaEtapa($caja->etapa);

        if ($siguiente === null) {
            throw ValidationException::withMessages([
                'etapa_destino' => $caja->codigo.' ya está en '.$etiquetaActual.'. El paso que sigue es registrar la entrega.',
            ]);
        }

        if ($destino !== $siguiente) {
            throw ValidationException::withMessages([
                'etapa_destino' => 'No se puede saltar etapas. '.$caja->codigo.' sigue en '.$etiquetaActual.'.',
            ]);
        }

        $desde = $etiquetaActual;
        $caja->etapa = $siguiente;
        $caja->estado = 'En '.Caja::etiquetaEtapa($siguiente);
        $caja->actividad = null;
        $caja->etapa_desde = now();
        $caja->proceso_desde = null;
        $caja->proceso_hasta = null;
        $caja->responsable_id = $marca['user_id'];
        $caja->save();

        $caja->anotaciones()->create([
            'user_id' => $marca['user_id'],
            'texto' => 'Pasó a '.Caja::etiquetaEtapa($siguiente),
        ]);

        Auditoria::registrar(
            'Avanzó etapa',
            'etapa',
            $caja->codigo,
            $desde.' → '.Caja::etiquetaEtapa($siguiente),
        );

        return [
            'caja' => $caja,
            'desde' => $desde,
            'hacia' => Caja::etiquetaEtapa($siguiente),
        ];
    }
}
