<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\Caja;
use App\Models\Ficha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    use ResuelveUsuario;

    /** @var list<string> */
    private const ORDEN_AREAS = [
        'Cirugía general',
        'Digestiva',
        'Bariátrica',
        'Niño',
        'Ginecología y obstetricia',
        'Cirugía vascular',
        'Urología',
        'Otorrino',
        'Traumatología',
        'Cirugía plástica',
        'Oftalmología',
        'Dental y maxilofacial',
    ];

    private function catalogoItems(): array
    {
        $desdeFichas = $this->catalogoDesdeFichas();
        if ($desdeFichas !== []) {
            return $desdeFichas;
        }

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
    /** @return list<array<string, mixed>> */
    private function catalogoLista(): array
    {
        if (! Ficha::query()->exists()) {
            return $this->catalogoItems();
        }

        return Ficha::query()
            ->with(['items.instrumento:id,nombre,codigo_fabricante'])
            ->orderBy('area')
            ->orderBy('nombre')
            ->withSum('items as piezas', 'cantidad')
            ->get()
            ->map(function (Ficha $ficha): array {
                $piezas = (int) $ficha->getAttribute('piezas');
                $contenido = $ficha->items
                    ->map(fn ($linea) => trim(($linea->instrumento?->codigo_fabricante ?? '').' '.($linea->instrumento?->nombre ?? '')))
                    ->implode(' ');

                return [
                    'codigo' => $ficha->codigo,
                    'codigo_referencia' => $ficha->codigo_referencia,
                    'numero' => $ficha->numero,
                    'nombre' => $ficha->nombre,
                    'tipo' => $ficha->tipo === 'set' ? 'Set quirúrgico' : 'Caja quirúrgica',
                    'servicio' => $ficha->area ?: 'Cirugía general',
                    'guia' => $piezas > 0,
                    'instrumentos' => [],
                    'piezas' => $piezas,
                    'buscar' => $contenido,
                ];
            })
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private function catalogoDesdeFichas(): array
    {
        if (! Ficha::query()->exists()) {
            return [];
        }

        return Ficha::query()
            ->with(['items.instrumento', 'servicioClinico'])
            ->orderBy('area')
            ->orderBy('nombre')
            ->get()
            ->map(function (Ficha $ficha): array {
                $instrumentos = $ficha->items->map(function ($linea): array {
                    $tamano = trim((string) $linea->instrumento?->tamano);

                    return [
                        'codigo' => $linea->instrumento?->codigo_fabricante ?? '',
                        'nombre' => trim(($linea->instrumento?->nombre ?? '').($tamano !== '' ? ' '.$tamano : '')),
                        'cantidad' => $linea->cantidad,
                        'imagen' => false,
                    ];
                })->all();

                return [
                    'codigo' => $ficha->codigo,
                    'codigo_referencia' => $ficha->codigo_referencia,
                    'numero' => $ficha->numero,
                    'nombre' => $ficha->nombre,
                    'tipo' => $ficha->tipo === 'set' ? 'Set quirúrgico' : 'Caja quirúrgica',
                    // Compatibilidad temporal: la vista actual agrupa por 'servicio',
                    // aunque ese valor corresponde al área quirúrgica.
                    // Se corregirá al actualizar admin.blade.php y sus filtros.
                    'servicio' => $ficha->area ?: 'Sin área asignada',
                    'area' => $ficha->area ?: 'Sin área asignada',
                    'servicio_real' => $ficha->servicioClinico?->nombre ?? 'Sin servicio asignado',
                    'servicio_id' => $ficha->servicio_id,
                    'guia' => $instrumentos !== [],
                    'instrumentos' => $instrumentos,
                    'piezas' => array_sum(array_column($instrumentos, 'cantidad')),
                ];
            })
            ->all();
    }

    public function catalogo(Request $request): View
    {
        $items = $this->catalogoLista();
        $busqueda = trim((string) $request->query('q', ''));
        $servicio = trim((string) $request->query('servicio', ''));
        $orden = array_flip(self::ORDEN_AREAS);
        $servicios = collect($items)->pluck('servicio')->unique()->sortBy(
            fn (string $nombre) => $orden[$nombre] ?? 99
        )->values()->all();
        $needle = mb_strtolower($busqueda);
        $porBusqueda = array_values(array_filter(
            $items,
            fn (array $item): bool => $this->coincideBusqueda($item, $needle)
        ));
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
        $etapas = $filtrados === []
            ? collect()
            : Caja::query()
                ->whereIn('codigo', array_column($filtrados, 'codigo'))
                ->pluck('etapa', 'codigo');
        $filtrados = array_map(function (array $item) use ($etapas): array {
            $etapa = $etapas[$item['codigo']] ?? null;
            $item['etapa_actual'] = $etapa ? Caja::etiquetaEtapa($etapa) : null;

            return $item;
        }, $filtrados);
        usort($filtrados, function (array $a, array $b) use ($orden): int {
            $area = ($orden[$a['servicio']] ?? 99) <=> ($orden[$b['servicio']] ?? 99);

            return $area !== 0 ? $area : strcasecmp((string) $a['nombre'], (string) $b['nombre']);
        });
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
        // Servicios reales: tabla servicios, no especialidades de fichas.area.
        $servicios = DB::table('servicios')->orderBy('nombre')->pluck('nombre')->all();
        $items = $this->catalogoItems();

        return view('catalogo.admin', [
            'usuario' => $this->usuarioSesion(),
            'items' => $items,
            'tipos' => [
                'Caja quirúrgica',
                'Set de instrumental',
                'Set de ópticas',
                'Set de accesorios',
                'Caja de implantes',
                'Otros',
            ],
            'servicios' => $servicios,
            'areas' => collect($items)->pluck('area')->filter()->unique()->sort()->values()->all(),
        ]);
    }

    public function ficha(string $codigo): View
    {
        $registro = Ficha::query()
            ->with(['items.instrumento', 'servicioClinico'])
            ->whereRaw('lower(codigo) = ?', [mb_strtolower($codigo)])
            ->first();
        if ($registro !== null) {
            $item = [
                'codigo' => $registro->codigo,
                'codigo_referencia' => $registro->codigo_referencia,
                'numero' => $registro->numero,
                'nombre' => $registro->nombre,
                'tipo' => $registro->tipo === 'set' ? 'Set quirúrgico' : 'Caja quirúrgica',
                'servicio' => $registro->area ?: ($registro->servicioClinico?->nombre ?? 'Central'),
                'guia' => $registro->items->isNotEmpty(),
                'instrumentos' => $registro->items->map(function ($linea): array {
                    $tamano = trim((string) $linea->instrumento?->tamano);

                    return [
                        'codigo' => $linea->instrumento?->codigo_fabricante ?? '',
                        'nombre' => trim(($linea->instrumento?->nombre ?? '').($tamano !== '' ? ' '.$tamano : '')),
                        'cantidad' => $linea->cantidad,
                        'imagen' => false,
                    ];
                })->all(),
                'piezas' => (int) $registro->items->sum('cantidad'),
            ];
        } else {
            $item = collect($this->catalogoItems())->first(
                fn (array $caja) => strcasecmp($caja['codigo'], $codigo) === 0
            );
        }
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
    /** @param  array<string, mixed>  $item */
    private function coincideBusqueda(array $item, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        $numero = ltrim((string) preg_replace('/\D/', '', (string) ($item['numero'] ?? '')), '0');
        $pedido = ltrim((string) preg_replace('/\D/', '', $needle), '0');
        if ($numero !== '' && $pedido !== '' && $numero === $pedido && preg_match('/^#?\s*0*\d+$/u', $needle) === 1) {
            return true;
        }

        $texto = mb_strtolower(implode(' ', array_filter([
            $item['codigo'] ?? '',
            $item['codigo_referencia'] ?? '',
            $item['numero'] ?? '',
            $numero,
            str_pad($numero, 2, '0', STR_PAD_LEFT),
            $item['nombre'] ?? '',
            $item['servicio'] ?? '',
            $item['tipo'] ?? '',
            $item['buscar'] ?? '',
        ])));

        return str_contains($texto, $needle);
    }

    private function conEtapaActual(array $item): array
    {
        $caja = Caja::query()->where('codigo', $item['codigo'])->first();
        $item['etapa_actual'] = $caja ? Caja::etiquetaEtapa($caja->etapa) : null;
        return $item;
    }
}
