<?php

namespace App\Services\Flujo;

use App\Models\Caja;
use App\Models\Ficha;
use App\Models\TipoConjunto;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecepcionCaja
{
    /**
     * Crea la caja del flujo únicamente a partir de una ficha válida del catálogo.
     *
     * @param  array{servicio_id: int, tipo_conjunto_id: int, ficha_id: int, quien_entrega: string, obs?: string|null}  $datos
     * @param  array{user_id: int|null, personal_id: int|null}  $marca
     * @return array{caja: Caja, ficha: Ficha, quien_entrega: string}
     */
    public function registrar(array $datos, array $marca): array
    {
        $ficha = Ficha::query()->findOrFail($datos['ficha_id']);
        $tipo = TipoConjunto::query()->findOrFail($datos['tipo_conjunto_id']);

        if ((int) $tipo->servicio_id !== (int) $datos['servicio_id']
            || (int) $ficha->servicio_id !== (int) $datos['servicio_id']
            || (int) $ficha->tipo_conjunto_id !== (int) $tipo->id) {
            throw ValidationException::withMessages([
                'ficha_id' => 'La caja no corresponde al servicio y tipo seleccionados.',
            ]);
        }

        if (Caja::query()->where('codigo', $ficha->codigo)->exists()) {
            throw ValidationException::withMessages([
                'ficha_id' => 'Esta caja ya está registrada en el flujo. No puede ingresarse dos veces.',
            ]);
        }

        $quienEntrega = trim($datos['quien_entrega']);

        $caja = DB::transaction(function () use ($ficha, $datos, $marca, $quienEntrega): Caja {
            // Bloquea la fila para evitar dos recepciones simultáneas del mismo código.
            $existente = Caja::query()->where('codigo', $ficha->codigo)->lockForUpdate()->first();
            if ($existente !== null) {
                throw ValidationException::withMessages([
                    'ficha_id' => 'Esta caja ya está registrada en el flujo.',
                ]);
            }

            $caja = Caja::query()->create([
                'codigo' => $ficha->codigo,
                'ficha_id' => $ficha->id,
                'servicio_id' => $ficha->servicio_id,
                'etapa' => Caja::ETAPA_RECEPCION,
                'ubicacion' => 'Área sucia',
                'estado' => 'En Recepción',
                'actividad' => null,
                'urgente' => false,
                'responsable_id' => $marca['user_id'],
                'etapa_desde' => now(),
            ]);

            $detalle = 'Recepción. Entrega: '.$quienEntrega.'.';
            if (! empty($datos['obs'])) {
                $detalle .= ' Observación: '.trim((string) $datos['obs']);
            }

            $caja->anotaciones()->create([
                'user_id' => $marca['user_id'],
                'texto' => $detalle,
            ]);

            return $caja;
        });

        return [
            'caja' => $caja,
            'ficha' => $ficha,
            'quien_entrega' => $quienEntrega,
        ];
    }
}
