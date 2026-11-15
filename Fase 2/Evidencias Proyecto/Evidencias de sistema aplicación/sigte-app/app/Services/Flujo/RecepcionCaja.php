<?php

namespace App\Services\Flujo;

use App\Models\Caja;
use App\Models\Ficha;
use App\Models\TipoConjunto;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecepcionCaja
{
    /**
     * Crea la caja del flujo únicamente a partir de una ficha válida del catálogo.
     * Un ciclo = una fila: si el código ya fue entregado, se crea un ciclo nuevo.
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

        if (Caja::enFlujo($ficha->codigo) !== null) {
            throw ValidationException::withMessages([
                'ficha_id' => 'Esta caja ya está registrada en el flujo. No puede ingresarse dos veces.',
            ]);
        }

        $quienEntrega = trim($datos['quien_entrega']);

        try {
            $caja = DB::transaction(function () use ($ficha, $datos, $marca, $quienEntrega): Caja {
                // Solo bloquea el ciclo activo; los entregados no impiden reingreso.
                $existente = Caja::query()
                    ->where('codigo', $ficha->codigo)
                    ->whereNull('entregado_at')
                    ->lockForUpdate()
                    ->first();
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
                    'personal_id' => $marca['personal_id'] ?? null,
                    'etapa_desde' => now(),
                ]);

                $detalle = 'Recepción. Entrega: '.$quienEntrega.'.';
                if (! empty($datos['obs'])) {
                    $detalle .= ' Observación: '.trim((string) $datos['obs']);
                }

                $caja->anotaciones()->create([
                    'user_id' => $marca['user_id'],
                    'personal_id' => $marca['personal_id'] ?? null,
                    'texto' => $detalle,
                ]);

                return $caja;
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (QueryException $e) {
            // Carrera: el unique parcial rechazó un segundo ciclo activo.
            if (str_contains(mb_strtolower($e->getMessage()), 'cajas_codigo_en_flujo_unique')) {
                throw ValidationException::withMessages([
                    'ficha_id' => 'Esta caja ya está registrada en el flujo.',
                ]);
            }
            throw $e;
        }

        return [
            'caja' => $caja,
            'ficha' => $ficha,
            'quien_entrega' => $quienEntrega,
        ];
    }
}
