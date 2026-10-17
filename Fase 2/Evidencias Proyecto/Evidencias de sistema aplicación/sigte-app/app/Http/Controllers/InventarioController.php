<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\Caja;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventarioController extends Controller
{
    use ResuelveUsuario;

    private function inventarioItems(): array
    {
        return [
            // Sala lavado
            [
                'sala' => 'lavado',
                'codigo' => 'DET-ENZ',
                'nombre' => 'Detergente enzimatico',
                'ubicacion' => 'Area sucia / remojo',
                'stock' => 8,
                'minimo' => 5,
                'en_proceso' => 0,
                'estado' => 'ok',
            ],
            [
                'sala' => 'lavado',
                'codigo' => 'EPP-KIT',
                'nombre' => 'Kit EPP (pechera/gorro/guantes)',
                'ubicacion' => 'Bodega area sucia',
                'stock' => 22,
                'minimo' => 15,
                'en_proceso' => 0,
                'estado' => 'ok',
            ],
            [
                'sala' => 'lavado',
                'codigo' => 'ESP-SEC',
                'nombre' => 'Esponjas / material secado',
                'ubicacion' => 'Puesto lavado',
                'stock' => 4,
                'minimo' => 6,
                'en_proceso' => 0,
                'estado' => 'bajo',
            ],
            // Sala armado
            [
                'sala' => 'armado',
                'codigo' => 'WRAP-SMS',
                'nombre' => 'Envoltorio SMS 60x60',
                'ubicacion' => 'Mesas de armado',
                'stock' => 35,
                'minimo' => 25,
                'en_proceso' => 0,
                'estado' => 'ok',
            ],
            [
                'sala' => 'armado',
                'codigo' => 'IND-CLASE5',
                'nombre' => 'Indicadores clase 5',
                'ubicacion' => 'Estante armado',
                'stock' => 12,
                'minimo' => 20,
                'en_proceso' => 0,
                'estado' => 'bajo',
            ],
            [
                'sala' => 'armado',
                'codigo' => 'CNT-EST-01',
                'nombre' => 'Contenedor rigido 1/1',
                'ubicacion' => 'Area preparacion',
                'stock' => 6,
                'minimo' => 4,
                'en_proceso' => 2,
                'estado' => 'ok',
            ],
            // Material esteril
            [
                'sala' => 'esteril',
                'codigo' => 'SET-LAP-01',
                'nombre' => 'Set laparoscopia basico',
                'ubicacion' => 'Almacen esteril A1',
                'stock' => 4,
                'minimo' => 3,
                'en_proceso' => 2,
                'estado' => 'ok',
            ],
            [
                'sala' => 'esteril',
                'codigo' => 'SET-CES-02',
                'nombre' => 'Set cesarea',
                'ubicacion' => 'Almacen esteril A2',
                'stock' => 2,
                'minimo' => 3,
                'en_proceso' => 1,
                'estado' => 'bajo',
            ],
            [
                'sala' => 'esteril',
                'codigo' => 'SET-UCI-03',
                'nombre' => 'Set via aerea UCI',
                'ubicacion' => 'Almacen esteril A3',
                'stock' => 1,
                'minimo' => 2,
                'en_proceso' => 1,
                'estado' => 'critico',
            ],
            [
                'sala' => 'esteril',
                'codigo' => 'CAJ-CUR-10',
                'nombre' => 'Caja curacion general',
                'ubicacion' => 'Almacen esteril B1',
                'stock' => 8,
                'minimo' => 5,
                'en_proceso' => 3,
                'estado' => 'ok',
            ],
        ];
    }
    public function inventario(): View
    {
        $items = $this->inventarioItems();
        $salas = [
            'lavado' => 'Sala lavado',
            'armado' => 'Sala armado',
            'esteril' => 'Material estéril',
        ];
        $sala = request()->query('sala', 'todos');
        if ($sala !== 'todos' && ! isset($salas[$sala])) {
            $sala = 'todos';
        }
        $busqueda = trim((string) request()->query('q', ''));
        $libros = [];
        foreach ($salas as $clave => $nombre) {
            $delLibro = collect($items)->where('sala', $clave);
            $libros[] = [
                'clave' => $clave,
                'nombre' => $nombre,
                'tipos' => $delLibro->count(),
                'stock' => (int) $delLibro->sum('stock'),
                'bajo' => $delLibro->whereIn('estado', ['bajo', 'critico'])->count(),
                'en_proceso' => (int) $delLibro->sum('en_proceso'),
            ];
        }
        $filtrados = $sala === 'todos'
            ? []
            : collect($items)
                ->where('sala', $sala)
                ->sortBy(fn (array $item) => ['critico' => 0, 'bajo' => 1, 'ok' => 2][$item['estado']] ?? 9)
                ->values()
                ->all();
        if ($sala !== 'todos' && $busqueda !== '') {
            $needle = mb_strtolower($busqueda);
            $filtrados = array_values(array_filter(
                $filtrados,
                function (array $item) use ($needle): bool {
                    $texto = mb_strtolower(
                        $item['codigo'].' '.$item['nombre'].' '.$item['ubicacion']
                    );
                    return str_contains($texto, $needle);
                }
            ));
        }
        $atencion = collect($items)->whereIn('estado', ['bajo', 'critico'])->values()->all();
        $rol = Auth::user()->nombreRol();
        return view('inventario.index', [
            'usuario' => $this->usuarioSesion(),
            'puedeReponer' => in_array($rol, [Rol::ENFERMERA, Rol::ADMINISTRADORA], true),
            'elemento' => trim((string) request()->query('elemento', '')),
            'busqueda' => $busqueda,
            'sala' => $sala,
            'salas' => $salas,
            'libros' => $libros,
            'items' => $filtrados,
            'atencion' => $atencion,
            'resumen' => [
                'tipos' => count($filtrados),
                'bajo_minimo' => collect($filtrados)->whereIn('estado', ['bajo', 'critico'])->count(),
                'en_almacen' => collect($filtrados)->sum('stock'),
                'en_proceso' => collect($filtrados)->sum('en_proceso'),
            ],
            'fecha' => now()->timezone('America/Santiago')->format('d/m/Y'),
            'hora' => now()->timezone('America/Santiago')->format('H:i'),
        ]);
    }
}
