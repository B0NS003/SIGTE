<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    use ResuelveUsuario;

    private function catalogoItems(): array
    {
        $items = [
            [
                'codigo' => 'SET-LAP-01',
                'nombre' => 'Set laparoscopia básico',
                'tipo' => 'Set quirúrgico',
                'servicio' => 'Pabellón',
                'guia' => true,
                'instrumentos' => [
                    ['nombre' => 'Trocar 5 mm', 'cantidad' => 4, 'imagen' => true],
                    ['nombre' => 'Trocar 10 mm', 'cantidad' => 2, 'imagen' => true],
                    ['nombre' => 'Pinza grasper', 'cantidad' => 4, 'imagen' => true],
                    ['nombre' => 'Pinza Maryland', 'cantidad' => 2, 'imagen' => false],
                    ['nombre' => 'Tijera', 'cantidad' => 2, 'imagen' => true],
                    ['nombre' => 'Cánula de aspiración', 'cantidad' => 2, 'imagen' => false],
                    ['nombre' => 'Óptica', 'cantidad' => 1, 'imagen' => true],
                    ['nombre' => 'Cable de luz', 'cantidad' => 1, 'imagen' => false],
                ],
            ],
            [
                'codigo' => 'SET-CES-02',
                'nombre' => 'Set cesárea',
                'tipo' => 'Set quirúrgico',
                'servicio' => 'Maternidad',
                'guia' => true,
                'instrumentos' => [
                    ['nombre' => 'Bisturí', 'cantidad' => 2, 'imagen' => true],
                    ['nombre' => 'Pinza Kocher', 'cantidad' => 6, 'imagen' => true],
                    ['nombre' => 'Pinza anatómica', 'cantidad' => 4, 'imagen' => true],
                    ['nombre' => 'Retractor', 'cantidad' => 2, 'imagen' => false],
                    ['nombre' => 'Portaagujas', 'cantidad' => 4, 'imagen' => true],
                    ['nombre' => 'Tijera Mayo', 'cantidad' => 2, 'imagen' => false],
                    ['nombre' => 'Separador', 'cantidad' => 4, 'imagen' => false],
                ],
            ],
            [
                'codigo' => 'CAJ-CUR-10',
                'nombre' => 'Caja curación general',
                'tipo' => 'Caja de curación',
                'servicio' => 'Curaciones',
                'guia' => true,
                'instrumentos' => [
                    ['nombre' => 'Pinza anatómica', 'cantidad' => 4, 'imagen' => true],
                    ['nombre' => 'Tijera', 'cantidad' => 2, 'imagen' => true],
                    ['nombre' => 'Riñonera', 'cantidad' => 2, 'imagen' => false],
                    ['nombre' => 'Gasas', 'cantidad' => 4, 'imagen' => false],
                ],
            ],
            [
                'codigo' => 'SET-UCI-03',
                'nombre' => 'Set vía aérea UCI',
                'tipo' => 'Set quirúrgico',
                'servicio' => 'UCI',
                'guia' => false,
                'instrumentos' => [
                    ['nombre' => 'Laringoscopio', 'cantidad' => 1, 'imagen' => true],
                    ['nombre' => 'Pinza Magill', 'cantidad' => 2, 'imagen' => true],
                    ['nombre' => 'Guía', 'cantidad' => 2, 'imagen' => false],
                    ['nombre' => 'Jeringa', 'cantidad' => 2, 'imagen' => false],
                    ['nombre' => 'Cánula', 'cantidad' => 2, 'imagen' => false],
                ],
            ],
            [
                'codigo' => 'CNT-EST-01',
                'nombre' => 'Contenedor rígido 1/1',
                'tipo' => 'Contenedor',
                'servicio' => 'General',
                'guia' => true,
                'instrumentos' => [
                    ['nombre' => 'Contenedor con filtro', 'cantidad' => 1, 'imagen' => true],
                ],
            ],
            [
                'codigo' => 'PKG-GM-05',
                'nombre' => 'Paquete grado médico pequeño',
                'tipo' => 'Paquete grado médico',
                'servicio' => 'Urgencia',
                'guia' => false,
                'instrumentos' => [
                    ['nombre' => 'Instrumental suelto empacado', 'cantidad' => 1, 'imagen' => false],
                ],
            ],
        ];
        return array_map(function (array $item): array {
            $item['piezas'] = array_sum(array_column($item['instrumentos'], 'cantidad'));
            return $item;
        }, $items);
    }
    public function catalogo(Request $request): View
    {
        $items = $this->catalogoItems();
        $busqueda = trim((string) $request->query('q', ''));
        $servicio = trim((string) $request->query('servicio', ''));
        $servicios = collect($items)->pluck('servicio')->unique()->sort()->values()->all();
        $needle = mb_strtolower($busqueda);
        $porBusqueda = array_values(array_filter($items, function (array $item) use ($needle): bool {
            if ($needle === '') {
                return true;
            }
            $texto = mb_strtolower($item['codigo'].' '.$item['nombre'].' '.$item['servicio'].' '.$item['tipo']);
            return str_contains($texto, $needle);
        }));
        $conteoServicios = [];
        foreach ($porBusqueda as $item) {
            $clave = $item['servicio'];
            $conteoServicios[$clave] = ($conteoServicios[$clave] ?? 0) + 1;
        }
        $filtrados = $porBusqueda;
        if ($servicio !== '') {
            $filtrados = array_values(array_filter(
                $porBusqueda,
                fn (array $item) => $item['servicio'] === $servicio
            ));
        }
        $filtrados = array_map(fn (array $item) => $this->conEtapaActual($item), $filtrados);
        return view('catalogo.index', [
            'usuario' => $this->usuarioSesion(),
            'items' => $filtrados,
            'busqueda' => $busqueda,
            'servicio' => $servicio,
            'servicios' => $servicios,
            'conteo_servicios' => $conteoServicios,
            'total_servicios' => count($porBusqueda),
            'hay_filtros' => $busqueda !== '' || $servicio !== '',
        ]);
    }
    public function catalogoAdmin(): View
    {
        return view('catalogo.admin', [
            'usuario' => $this->usuarioSesion(),
            'items' => $this->catalogoItems(),
            'tipos' => ['Set quirúrgico', 'Caja de curación', 'Contenedor', 'Paquete grado médico'],
            'servicios' => ['Pabellón', 'Maternidad', 'Curaciones', 'UCI', 'General', 'Urgencia'],
        ]);
    }
    public function ficha(string $codigo): View
    {
        $item = collect($this->catalogoItems())->first(
            fn (array $caja) => strcasecmp($caja['codigo'], $codigo) === 0
        );
        if ($item !== null) {
            $item = $this->conEtapaActual($item);
        }
        return view('catalogo.ficha', [
            'usuario' => $this->usuarioSesion(),
            'item' => $item,
            'codigo' => $codigo,
        ]);
    }
    /**
     * La etapa pertenece al ejemplar en proceso, no a la definición del set.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function conEtapaActual(array $item): array
    {
        $caja = Caja::query()->where('codigo', $item['codigo'])->first();
        $item['etapa_actual'] = $caja ? Caja::etiquetaEtapa($caja->etapa) : null;
        return $item;
    }
}
