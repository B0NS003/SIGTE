<?php

namespace App\Support;

use App\Models\Caja;
use App\Models\CajaAnotacion;
use App\Models\CajaRetroceso;
use App\Models\RegistroAuditoria;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CadenaCustodia
{
    /**
     * Cadena de una o más cajas por código. Sin código no se lista nada:
     * la bitácora es la consulta principal; la custodia se pide al buscar.
     *
     * @return list<array{caja: string, servicio: string, estado: string, ubicacion: string, responsable: string, alerta: bool, eventos: list<array{hora: string, tipo: string, de: string, a: string, nota: string}>}>
     */
    public static function buscar(string $codigo): array
    {
        $codigo = trim($codigo);
        if ($codigo === '') {
            return [];
        }

        $cajas = Caja::query()
            ->with([
                'responsable:id,name,email',
                'persona:id,nombre',
                'anotaciones' => fn ($q) => $q->with(['autor:id,name,email', 'persona:id,nombre'])->orderBy('id'),
                'retrocesos' => fn ($q) => $q->with(['autor:id,name,email'])->orderBy('id'),
            ])
            ->where('codigo', 'ilike', '%'.$codigo.'%')
            ->orderBy('codigo')
            ->limit(20)
            ->get();

        if ($cajas->isEmpty()) {
            return [];
        }

        $entregas = RegistroAuditoria::query()
            ->with('actor:id,name,email')
            ->where('tipo', 'entrega')
            ->whereIn('objeto', $cajas->pluck('codigo'))
            ->orderBy('created_at')
            ->get()
            ->groupBy('objeto');

        $cadenas = [];
        foreach ($cajas as $caja) {
            $cadenas[] = self::deCaja($caja, $entregas->get($caja->codigo, collect()));
        }

        return $cadenas;
    }

    /**
     * @param  Collection<int, RegistroAuditoria>  $entregas
     * @return array{caja: string, servicio: string, estado: string, ubicacion: string, responsable: string, alerta: bool, eventos: list<array{hora: string, tipo: string, de: string, a: string, nota: string}>}
     */
    private static function deCaja(Caja $caja, Collection $entregas): array
    {
        $eventos = [];
        $visto = [];

        foreach ($caja->anotaciones as $nota) {
            $evento = self::eventoDesdeAnotacion($nota);
            $clave = $evento['tipo'].'|'.$evento['nota'].'|'.$evento['momento']->format('Y-m-d H:i');
            if (isset($visto[$clave])) {
                continue;
            }
            $visto[$clave] = true;
            $eventos[] = $evento;
        }

        foreach ($caja->retrocesos as $retroceso) {
            $eventos[] = self::eventoDesdeRetroceso($retroceso);
        }

        if ($caja->entregado_at !== null) {
            $entrega = $entregas->last();
            $eventos[] = [
                'momento' => $caja->entregado_at,
                'tipo' => 'Entrega',
                'de' => self::nombreActor($entrega?->actor, $caja->persona?->nombre),
                'a' => '—',
                'nota' => $entrega?->detalle ?? ('Entregada a '.$caja->servicio),
            ];
        }

        if ($eventos === []) {
            $eventos[] = [
                'momento' => $caja->etapa_desde ?? $caja->updated_at ?? now(),
                'tipo' => 'En etapa',
                'de' => self::limpiarNombre($caja->nombreResponsable()),
                'a' => '—',
                'nota' => 'Sin movimientos registrados todavía. Está en '.Caja::etiquetaEtapa((string) $caja->etapa).'.',
            ];
        }

        usort($eventos, fn (array $a, array $b) => $a['momento'] <=> $b['momento']);

        $formateados = [];
        foreach ($eventos as $evento) {
            $momento = Carbon::parse($evento['momento'])->timezone('America/Santiago');
            $formateados[] = [
                'hora' => self::etiquetaHora($momento),
                'tipo' => $evento['tipo'],
                'de' => $evento['de'],
                'a' => $evento['a'],
                'nota' => $evento['nota'],
            ];
        }

        $entregada = $caja->entregado_at !== null;

        return [
            'caja' => $caja->codigo,
            'servicio' => $caja->servicio,
            'estado' => $entregada ? 'Entregada' : Caja::etiquetaEtapa((string) $caja->etapa),
            'ubicacion' => (string) ($caja->ubicacion ?: '—'),
            'responsable' => self::limpiarNombre($caja->nombreResponsable()),
            'alerta' => (bool) $caja->urgente && ! $entregada,
            'eventos' => $formateados,
        ];
    }

    /** @return array{momento: Carbon, tipo: string, de: string, a: string, nota: string} */
    private static function eventoDesdeAnotacion(CajaAnotacion $nota): array
    {
        $texto = trim((string) $nota->texto);
        $tipo = 'Anotación';
        if (str_starts_with($texto, 'Pasó a ')) {
            $tipo = 'Avance';
        } elseif (str_starts_with($texto, 'Volvió a ')) {
            $tipo = 'Corrección';
        }

        return [
            'momento' => $nota->created_at ?? now(),
            'tipo' => $tipo,
            'de' => self::nombreActor($nota->autor, $nota->persona?->nombre),
            'a' => '—',
            'nota' => $texto !== '' ? $texto : 'Sin detalle',
        ];
    }

    /** @return array{momento: Carbon, tipo: string, de: string, a: string, nota: string} */
    private static function eventoDesdeRetroceso(CajaRetroceso $retroceso): array
    {
        $desde = Caja::etiquetaEtapa((string) $retroceso->etapa_desde);
        $hacia = Caja::etiquetaEtapa((string) $retroceso->etapa_hacia);
        $motivo = trim((string) $retroceso->motivo);

        return [
            'momento' => $retroceso->created_at ?? now(),
            'tipo' => 'Corrección',
            'de' => self::nombreActor($retroceso->autor),
            'a' => '—',
            'nota' => trim($desde.' → '.$hacia.($motivo !== '' ? '. '.$motivo : '')),
        ];
    }

    private static function nombreActor(?User $user, ?string $persona = null): string
    {
        if ($persona !== null && trim($persona) !== '') {
            return trim($persona);
        }
        if ($user === null) {
            return 'Sin responsable';
        }
        if ($user->esCuentaDelPuesto()) {
            return 'Cuenta del puesto';
        }

        return self::limpiarNombre($user->name);
    }

    private static function limpiarNombre(string $nombre): string
    {
        $nombre = trim($nombre);
        if ($nombre === '' || strcasecmp($nombre, 'Operador Demo') === 0) {
            return 'Cuenta del puesto';
        }

        return $nombre;
    }

    private static function etiquetaHora(Carbon $momento): string
    {
        $hoy = now()->timezone('America/Santiago')->toDateString();
        if ($momento->toDateString() === $hoy) {
            return $momento->format('H:i');
        }

        return $momento->format('d/m H:i');
    }
}
