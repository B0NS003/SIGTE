<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\CajaAnotacion;
use App\Models\ConsumoServicio;
use App\Models\Insumo;
use App\Models\Rol;
use App\Support\AccesoPorRol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MockupController extends Controller
{
    private function fases(): array
    {
        return array_map(
            fn (string $etapa) => Caja::etiquetaEtapa($etapa),
            Caja::etapas()
        );
    }

    /**
     * Cajas desde PostgreSQL, en el formato que usan las vistas del mockup.
     *
     * @return list<array{id: string, servicio: string, fase_idx: int, estado: string, ubicacion: string, operadora: string, fecha: string, hora: string, tiempo: string, minutos: int|null, urgente: bool}>
     */
    private function cajas(): array
    {
        return Caja::query()
            ->with('responsable')
            ->orderByDesc('urgente')
            ->orderBy('etapa_desde')
            ->get()
            ->map(function (Caja $caja) {
                return [
                    'id' => $caja->codigo,
                    'servicio' => $caja->servicio,
                    'fase_idx' => $caja->indiceEtapa(),
                    'estado' => $caja->estado ?? Caja::etiquetaEtapa($caja->etapa),
                    'ubicacion' => $caja->ubicacion ?? 'Sin ubicacion',
                    'operadora' => $caja->nombreResponsable(),
                    'fecha' => $caja->etapa_desde?->timezone('America/Santiago')->format('d/m/Y') ?? '—',
                    'hora' => $caja->etapa_desde?->timezone('America/Santiago')->format('H:i') ?? '—',
                    'tiempo' => $caja->tiempoEnEtapa(),
                    'etapa_iso' => $caja->etapa_desde?->toIso8601String(),
                    'minutos' => $caja->etapa_desde === null
                        ? null
                        : (int) abs($caja->etapa_desde->diffInMinutes(now())),
                    'urgente' => $caja->urgente,
                    'proceso_desde' => $caja->proceso_desde?->toIso8601String(),
                    'proceso_hasta' => $caja->proceso_hasta?->toIso8601String(),
                    'proceso_listo' => $caja->procesoListo(),
                ];
            })
            ->all();
    }

    /**
     * Lo que la jefatura puede ver con la etapa actual. No hay historial de pasos.
     *
     * @param  list<array{id: string, servicio: string, fase_idx: int, tiempo: string, minutos: int|null}>  $cajas
     * @return array{total: int, en_almacen: int, en_entrega: int, conteo: list<int>, detenidas: list<array{id: string, servicio: string, etapa: string, lleva: string, limite: int}>}
     */
    private function panorama(array $cajas): array
    {
        $fases = $this->fases();
        $etapas = Caja::etapas();
        $guia = [
            Caja::ETAPA_RECEPCION => 60,
            Caja::ETAPA_LAVADO => 60,
            Caja::ETAPA_PREPARACION => 90,
            Caja::ETAPA_ESTERILIZACION => 45,
            Caja::ETAPA_ALMACEN => 240,
            Caja::ETAPA_ENTREGA => 60,
        ];
        $porEtapa = [];
        $detenidas = [];
        foreach ($etapas as $indice => $clave) {
            $porEtapa[$indice] = [
                'indice' => $indice,
                'nombre' => $fases[$indice],
                'clave' => $clave,
                'cantidad' => 0,
                'tarde' => 0,
                'cajas' => [],
            ];
        }

        foreach ($cajas as $caja) {
            $indice = $caja['fase_idx'];
            $etapa = $etapas[$indice] ?? Caja::ETAPA_RECEPCION;
            $limite = $guia[$etapa] ?? 60;
            $tarde = $caja['minutos'] !== null && $caja['minutos'] > $limite;
            $porEtapa[$indice]['cantidad']++;
            if ($tarde) {
                $porEtapa[$indice]['tarde']++;
                $detenidas[] = [
                    'id' => $caja['id'],
                    'servicio' => $caja['servicio'],
                    'etapa' => $fases[$indice],
                    'clave' => $etapa,
                    'espera' => $this->etiquetaEspera($caja['minutos']),
                    'minutos' => $caja['minutos'],
                ];
            }
            $porEtapa[$indice]['cajas'][] = [
                'id' => $caja['id'],
                'servicio' => $caja['servicio'],
                'espera' => $caja['minutos'] === null ? 'Sin registro' : $this->etiquetaEspera($caja['minutos']),
                'tarde' => $tarde,
                'minutos' => $caja['minutos'] ?? -1,
            ];
        }

        foreach ($porEtapa as &$grupo) {
            usort($grupo['cajas'], fn (array $a, array $b) => $b['minutos'] <=> $a['minutos']);
        }
        unset($grupo);

        usort($detenidas, fn (array $a, array $b) => $b['minutos'] <=> $a['minutos']);

        return [
            'total' => count($cajas),
            'en_almacen' => $porEtapa[array_search(Caja::ETAPA_ALMACEN, $etapas, true)]['cantidad'] ?? 0,
            'en_entrega' => $porEtapa[array_search(Caja::ETAPA_ENTREGA, $etapas, true)]['cantidad'] ?? 0,
            'conteo' => array_column($porEtapa, 'cantidad'),
            'detenidas' => $detenidas,
            'etapas' => array_values($porEtapa),
        ];
    }

    private function etiquetaEspera(int $minutos): string
    {
        if ($minutos < 60) {
            return $minutos.' min';
        }

        if ($minutos < 60 * 24) {
            $horas = intdiv($minutos, 60);

            return $horas === 1 ? '1 hora' : $horas.' horas';
        }

        $dias = intdiv($minutos, 60 * 24);

        return $dias === 1 ? '1 día' : $dias.' días';
    }

    /**
     * Consulta de cajas para HIU-EP2-001: búsqueda, filtro por etapa y agrupación.
     *
     * @return array{
     *   busqueda: string,
     *   etapa_filtro: int|null,
     *   cajas: list<array>,
     *   cajas_por_etapa: list<array{indice: int, nombre: string, cajas: list<array>}>,
     *   conteo_etapas: list<int>,
     *   total: int,
     *   hay_filtros: bool
     * }
     */
    private function consultaCajas(Request $request): array
    {
        $fases = $this->fases();
        $todas = $this->cajas();
        $busqueda = trim((string) $request->query('q', ''));
        $etapaRaw = $request->query('etapa');
        $etapaFiltro = is_numeric($etapaRaw) ? (int) $etapaRaw : null;

        if ($etapaFiltro !== null && ($etapaFiltro < 0 || $etapaFiltro >= count($fases))) {
            $etapaFiltro = null;
        }

        $filtradas = $todas;

        if ($busqueda !== '') {
            $needle = mb_strtolower($busqueda);
            $filtradas = array_values(array_filter($filtradas, function (array $caja) use ($needle) {
                $haystack = mb_strtolower(implode(' ', [
                    $caja['id'],
                    $caja['servicio'],
                    $caja['ubicacion'],
                    $caja['estado'],
                    $caja['operadora'],
                ]));

                return str_contains($haystack, $needle);
            }));
        }

        $conteoEtapas = array_fill(0, count($fases), 0);
        foreach ($filtradas as $caja) {
            $conteoEtapas[$caja['fase_idx']]++;
        }

        if ($etapaFiltro !== null) {
            $filtradas = array_values(array_filter(
                $filtradas,
                fn (array $caja) => $caja['fase_idx'] === $etapaFiltro
            ));
        }

        $cajasPorEtapa = [];
        foreach ($fases as $indice => $nombre) {
            $delGrupo = array_values(array_filter(
                $filtradas,
                fn (array $caja) => $caja['fase_idx'] === $indice
            ));

            if ($delGrupo === []) {
                continue;
            }

            $cajasPorEtapa[] = [
                'indice' => $indice,
                'nombre' => $nombre,
                'cajas' => $delGrupo,
            ];
        }

        return [
            'busqueda' => $busqueda,
            'etapa_filtro' => $etapaFiltro,
            'cajas' => $filtradas,
            'cajas_por_etapa' => $cajasPorEtapa,
            'conteo_etapas' => $conteoEtapas,
            'total' => count($filtradas),
            'hay_filtros' => $busqueda !== '' || $etapaFiltro !== null,
        ];
    }


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


    private function usuarioSesion(): array
    {
        $user = Auth::user();
        $etiqueta = [
            Rol::ADMINISTRADORA => 'Administradora',
            Rol::ENFERMERA => 'Enfermera de turno',
            Rol::OPERADOR => 'Operadora',
            Rol::SECRETARIA => 'Secretaria',
        ][$user->nombreRol()] ?? 'Sin rol';

        return [
            'nombre' => $user->name,
            'rol' => $etiqueta,
        ];
    }

    public function panel(Request $request, string $rol): View|RedirectResponse
    {
        $user = Auth::user();
        $rolReal = $user->nombreRol();

        if ($rolReal === null || ! in_array($rolReal, [Rol::ADMINISTRADORA, Rol::ENFERMERA, Rol::OPERADOR, Rol::SECRETARIA], true)) {
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta no tiene un rol válido.',
            ]);
        }

        if (! AccesoPorRol::puedeVerPanel($user, $rol)) {
            return redirect()->route('mockups.panel', $rolReal);
        }

        $usuario = $this->usuarioSesion();

        if ($rol === Rol::SECRETARIA) {
            $inicio = now()->startOfMonth()->toDateString();
            $delMes = ConsumoServicio::query()->whereDate('periodo', $inicio)->get();
            $insumos = Insumo::query()->get();

            return view('mockups.panel-secretaria', [
                'usuario' => $usuario,
                'resumen' => [
                    'periodo' => ConsumoServicio::etiquetaPeriodo($inicio),
                    'servicios' => $delMes->count(),
                    'consumo' => (int) $delMes->sum('consumo'),
                    'litros' => (float) $delMes->sum('litros'),
                    'bajo_minimo' => $insumos->filter(fn (Insumo $insumo) => $insumo->estadoStock() !== 'ok')->count(),
                ],
            ]);
        }

        $roles = [
            Rol::ADMINISTRADORA => [
                'rol' => 'Administradora',
                'vista' => 'mockups.panel-admin',
            ],
            Rol::ENFERMERA => [
                'rol' => 'Enfermera de turno',
                'vista' => 'mockups.panel-enfermera',
            ],
            Rol::OPERADOR => [
                'rol' => 'Operadora',
                'vista' => 'mockups.panel-operador',
            ],
        ];

        $vista = $roles[$rol]['vista'];
        $fases = $this->fases();

        if ($rol === Rol::OPERADOR) {
            $consulta = $this->consultaCajas($request);
        } else {
            $todas = $this->cajas();
            $consulta = [
                'busqueda' => '',
                'etapa_filtro' => null,
                'cajas' => $todas,
                'cajas_por_etapa' => [],
                'conteo_etapas' => [],
                'total' => count($todas),
                'hay_filtros' => false,
            ];
        }

        return view($vista, [
            'usuario' => $usuario,
            'rol_key' => $rol,
            'fases' => $fases,
            'cajas' => $consulta['cajas'],
            'cajas_por_etapa' => $consulta['cajas_por_etapa'],
            'busqueda' => $consulta['busqueda'],
            'etapa_filtro' => $consulta['etapa_filtro'],
            'conteo_etapas' => $consulta['conteo_etapas'],
            'total_consulta' => $consulta['total'],
            'hay_filtros' => $consulta['hay_filtros'],
            'kpis' => [
                ['label' => 'En flujo', 'value' => 28, 'hint' => 'Cajas/sets activos', 'tone' => ''],
                ['label' => 'Listas para entrega', 'value' => 12, 'hint' => 'En almacen esteril', 'tone' => 'ok'],
                ['label' => 'Entregadas hoy', 'value' => 19, 'hint' => 'Con custodia', 'tone' => ''],
                ['label' => 'Requieren atencion', 'value' => 3, 'hint' => 'Retraso o faltante', 'tone' => 'warn'],
            ],
            'alertas' => [
                ['nivel' => 'alta', 'titulo' => 'SET-042 lleva 4h en esterilizacion', 'detalle' => 'Pabellon · Autoclave 2'],
                ['nivel' => 'media', 'titulo' => 'CAJA-118 sin custodia al salir de lavado', 'detalle' => 'Urgencia'],
                ['nivel' => 'media', 'titulo' => 'Faltante en SET-007 (1 pinza)', 'detalle' => 'Detectado en preparacion'],
            ],
            'etiquetas_etapa' => array_map(
                fn (string $etapa) => Caja::etiquetaEtapa($etapa),
                Caja::etapas()
            ),
            'panorama' => $this->panorama($consulta['cajas']),
            'falla_produccion' => $request->boolean('falla'),
            'accion_modal' => in_array($request->query('accion'), ['etapa', 'actividad'], true)
                ? 'etapa'
                : null,
            'caja_modal' => trim((string) $request->query('caja', '')),
            'pasos_etapa' => $this->pasosPorEtapa(),
            'anotaciones_por_caja' => $rol === Rol::OPERADOR ? $this->anotacionesPorCaja() : [],
            'ahora_modal' => [
                'fecha' => now()->timezone('America/Santiago')->format('d/m/Y'),
                'hora' => now()->timezone('America/Santiago')->format('H:i'),
            ],
        ]);
    }
    public function recepcion(): View
    {
        return view('mockups.recepcion', [
            'usuario' => $this->usuarioSesion(),
            'servicios' => ['Pabellon', 'Urgencia', 'Maternidad', 'UCI', 'Curaciones', 'Otro servicio'],
            'tipos' => ['Set quirurgico', 'Caja de curacion', 'Contenedor', 'Paquete grado medico'],
        ]);
    }

    public function avanzar(Request $request): RedirectResponse
    {
        return redirect()->route('mockups.panel', array_filter([
            'rol' => Rol::OPERADOR,
            'accion' => 'etapa',
            'caja' => trim((string) $request->query('caja', '')) ?: null,
        ]));
    }

    /**
     * HIU-EP2-002: avanza una sola etapa y deja fecha, hora y usuario.
     */
    public function guardarAvance(Request $request): RedirectResponse
    {
        $codigo = trim((string) $request->input('caja', ''));
        $destino = trim((string) $request->input('etapa_destino', ''));
        $volver = $this->volverAlFlujo($request, $codigo);

        if ($codigo === '') {
            return $volver->withErrors(['caja' => 'Elige la caja que vas a pasar.']);
        }

        $caja = Caja::query()->where('codigo', $codigo)->first();
        if ($caja === null) {
            return $volver->withErrors(['caja' => 'Esa caja no está en el flujo.']);
        }

        $etapas = Caja::etapas();
        $indice = $caja->indiceEtapa();
        $siguiente = $etapas[$indice + 1] ?? null;
        $etiquetaActual = Caja::etiquetaEtapa($caja->etapa);

        if ($destino === '') {
            return $volver->withErrors([
                'etapa_destino' => 'Falta la etapa a la que pasa la caja.',
            ]);
        }

        if ($siguiente === null) {
            return $volver->withErrors([
                'etapa_destino' => $caja->codigo.' ya está en '.$etiquetaActual.'. El paso que sigue es registrar la entrega.',
            ]);
        }

        if ($destino !== $siguiente) {
            return $volver->withErrors([
                'etapa_destino' => 'No se puede saltar etapas. '.$caja->codigo.' sigue en '.$etiquetaActual.'.',
            ]);
        }

        $caja->etapa = $siguiente;
        $caja->estado = 'En '.Caja::etiquetaEtapa($siguiente);
        $caja->etapa_desde = now();
        $caja->proceso_desde = null;
        $caja->proceso_hasta = null;
        $caja->responsable_id = Auth::id();
        $caja->save();

        $caja->anotaciones()->create([
            'user_id' => Auth::id(),
            'texto' => 'Pasó a '.Caja::etiquetaEtapa($siguiente),
        ]);

        return $this->volverAlFlujo($request, $caja->codigo, false)
            ->with('ok', 'Listo. '.$caja->codigo.' está en '.Caja::etiquetaEtapa($siguiente).'.');
    }

    private function volverAlFlujo(Request $request, string $codigo, bool $conservarEtapa = true): RedirectResponse
    {
        $params = [
            'rol' => Rol::OPERADOR,
            'accion' => 'etapa',
        ];

        if ($codigo !== '') {
            $params['caja'] = $codigo;
        }

        $busqueda = trim((string) $request->input('q', ''));
        if ($busqueda !== '') {
            $params['q'] = $busqueda;
        }

        $etapa = $request->input('etapa');
        if ($conservarEtapa && $etapa !== null && $etapa !== '') {
            $params['etapa'] = $etapa;
        }

        return redirect()->route('mockups.panel', $params);
    }

    /**
     * HIU-EP2-004: elegir la caja del flujo y mostrar la etapa en la que ya está.
     * La actividad dentro de esa etapa se agrega en las tareas siguientes.
     */
    public function actividad(Request $request): RedirectResponse
    {
        return redirect()->route('mockups.panel', array_filter([
            'rol' => Rol::OPERADOR,
            'accion' => 'etapa',
            'caja' => trim((string) $request->query('caja', '')) ?: null,
        ]));
    }

    public function guardarAnotacion(Request $request): JsonResponse
    {
        $codigo = trim((string) $request->input('caja', ''));
        $texto = trim((string) $request->input('texto', ''));
        $caja = Caja::query()->where('codigo', $codigo)->first();

        if ($caja === null) {
            return response()->json(['mensaje' => 'Esa caja no está en el flujo.'], 422);
        }

        if (! in_array($texto, $this->textosAnotables($caja->etapa), true)) {
            return response()->json(['mensaje' => 'Esa anotación no corresponde a esta etapa.'], 422);
        }

        $nota = $caja->anotaciones()->create([
            'user_id' => Auth::id(),
            'texto' => $texto,
        ]);
        $nota->load('autor');

        $minutos = $this->minutosDeTexto($caja->etapa, $texto);
        if ($minutos !== null) {
            $caja->sumarProceso($minutos);
            $caja->save();
        }

        return response()->json([
            'texto' => $nota->texto,
            'cuando' => $nota->created_at->timezone('America/Santiago')->format('d/m/Y · H:i'),
            'quien' => $nota->autor?->name ?? '',
            'proceso_desde' => $caja->proceso_desde?->toIso8601String(),
            'proceso_hasta' => $caja->proceso_hasta?->toIso8601String(),
        ]);
    }

    /**
     * @return array<string, list<array{texto: string, cuando: string, quien: string}>>
     */
    private function anotacionesPorCaja(): array
    {
        $grupos = [];

        CajaAnotacion::query()
            ->with(['caja:id,codigo', 'autor:id,name'])
            ->orderBy('id')
            ->get()
            ->each(function (CajaAnotacion $nota) use (&$grupos): void {
                $codigo = $nota->caja?->codigo;
                if ($codigo === null) {
                    return;
                }

                $grupos[$codigo][] = [
                    'texto' => $nota->texto,
                    'cuando' => $nota->created_at->timezone('America/Santiago')->format('d/m/Y · H:i'),
                    'quien' => $nota->autor?->name ?? '',
                ];
            });

        return $grupos;
    }

    /** @return list<string> */
    private function textosAnotables(string $etapa): array
    {
        $paso = $this->pasosPorEtapa()[$etapa] ?? ['opciones' => [], 'tiempos' => []];

        return array_merge(
            $paso['opciones'],
            array_column($paso['tiempos'], 'texto')
        );
    }

    private function minutosDeTexto(string $etapa, string $texto): ?int
    {
        foreach ($this->pasosPorEtapa()[$etapa]['tiempos'] ?? [] as $tiempo) {
            if ($tiempo['texto'] === $texto) {
                return (int) $tiempo['minutos'];
            }
        }

        return null;
    }

    /**
     * Cada tramo anota otra cosa. El tiempo solo aparece donde la central lo escribe a mano.
     *
     * @return array<string, array{tramo: string, ayuda: string, opciones: list<string>, tiempos: list<array{texto: string, etiqueta: string}>}>
     */
    private function pasosPorEtapa(): array
    {
        return [
            Caja::ETAPA_RECEPCION => [
                'tramo' => 'Recepción a lavado',
                'resumen' => 'De dónde llega y si ya quedó en remojo.',
                'ayuda' => 'Se anota de qué servicio llega y si ya quedó en remojo. En este paso no hay ciclo de máquina.',
                'opciones' => ['Recepción del servicio', 'Remojo'],
                'tiempos' => [],
            ],
            Caja::ETAPA_LAVADO => [
                'tramo' => 'Lavado a preparación',
                'resumen' => 'Lavado, secado y la hora de la lavadora.',
                'ayuda' => 'La lavadora, en el ciclo estándar, se demora una hora. Si falla, ese tiempo se anota de nuevo.',
                'opciones' => ['Lavado', 'Secado'],
                'tiempos' => [
                    ['texto' => 'Ciclo de lavadora: 60 min', 'etiqueta' => 'Lavadora · 60 min', 'minutos' => 60],
                ],
            ],
            Caja::ETAPA_PREPARACION => [
                'tramo' => 'Preparación a esterilización',
                'resumen' => 'Inspección, reconteo y quién lo armó.',
                'ayuda' => 'Inspección, reconteo y rotulado de quien armó. Todavía no entra al autoclave.',
                'opciones' => ['Inspección', 'Reconteo', 'Armado y rotulado'],
                'tiempos' => [],
            ],
            Caja::ETAPA_ESTERILIZACION => [
                'tramo' => 'Esterilización a almacén',
                'resumen' => 'La carga, el voucher y la espera para abrir.',
                'ayuda' => 'En el voucher van la temperatura, el tiempo y quién lo tiró. Se copia a mano. Antes de abrir la puerta se esperan 20 minutos.',
                'opciones' => ['Carga del autoclave', 'Voucher de la carga'],
                'tiempos' => [
                    ['texto' => 'Espera para abrir: 20 min', 'etiqueta' => 'Abrir puerta · 20 min', 'minutos' => 20],
                ],
            ],
            Caja::ETAPA_ALMACEN => [
                'tramo' => 'Almacén a entrega',
                'resumen' => 'La descarga y cuánto se dejó enfriar.',
                'ayuda' => 'Al descargar se espera que enfríe: media hora el material chico y una hora el contenedor.',
                'opciones' => ['Descarga', 'Enfriamiento'],
                'tiempos' => [
                    ['texto' => 'Enfriamiento material chico: 30 min', 'etiqueta' => 'Material chico · 30 min', 'minutos' => 30],
                    ['texto' => 'Enfriamiento contenedor: 60 min', 'etiqueta' => 'Contenedor · 60 min', 'minutos' => 60],
                ],
            ],
            Caja::ETAPA_ENTREGA => [
                'tramo' => 'Entrega',
                'resumen' => 'El servicio, la hora y quién retira.',
                'ayuda' => 'En el libro de salida van el servicio, la hora y quién entrega.',
                'opciones' => ['Entrega al servicio'],
                'tiempos' => [],
            ],
        ];
    }

    public function entrega(Request $request): View
    {
        $listas = collect($this->cajas())->where('fase_idx', 4)->values()->all();
        $marcada = trim((string) $request->query('caja', ''));
        $caja = collect($listas)->firstWhere('id', $marcada);

        return view('mockups.entrega', [
            'usuario' => $this->usuarioSesion(),
            'listas' => $listas,
            'caja' => $caja,
            'servicios' => ['Pabellon', 'Urgencia', 'Maternidad', 'UCI', 'Curaciones', 'Otro servicio'],
            'materiales' => ['Paquete de ropa', 'Material de curación', 'Instrumental suelto'],
            'fecha' => now()->timezone('America/Santiago')->format('d/m/Y'),
            'hora' => now()->timezone('America/Santiago')->format('H:i'),
        ]);
    }

    public function catalogo(Request $request): View
    {
        $items = $this->catalogoItems();
        $busqueda = trim((string) $request->query('q', ''));
        $servicio = trim((string) $request->query('servicio', ''));
        $servicios = collect($items)->pluck('servicio')->unique()->sort()->values()->all();
        $needle = mb_strtolower($busqueda);

        $filtrados = array_values(array_filter($items, function (array $item) use ($needle, $servicio): bool {
            if ($servicio !== '' && $item['servicio'] !== $servicio) {
                return false;
            }

            if ($needle === '') {
                return true;
            }

            $texto = mb_strtolower($item['codigo'].' '.$item['nombre'].' '.$item['servicio'].' '.$item['tipo']);

            return str_contains($texto, $needle);
        }));

        $filtrados = array_map(fn (array $item) => $this->conEtapaActual($item), $filtrados);

        return view('mockups.catalogo', [
            'usuario' => $this->usuarioSesion(),
            'items' => $filtrados,
            'busqueda' => $busqueda,
            'servicio' => $servicio,
            'servicios' => $servicios,
            'hay_filtros' => $busqueda !== '' || $servicio !== '',
        ]);
    }

    public function catalogoAdmin(): View
    {
        return view('mockups.catalogo-admin', [
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

        return view('mockups.ficha-caja', [
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
        $atencion = collect($items)->whereIn('estado', ['bajo', 'critico'])->values()->all();
        $rol = Auth::user()->nombreRol();

        return view('mockups.inventario', [
            'usuario' => $this->usuarioSesion(),
            'puedeReponer' => in_array($rol, [Rol::ENFERMERA, Rol::ADMINISTRADORA], true),
            'elemento' => trim((string) request()->query('elemento', '')),
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

    public function usuarios(): View
    {
        $usuarios = [
            [
                'nombre' => 'Natalia Sanchez',
                'email' => 'nsanchez@hsjmelipilla.cl',
                'rol' => 'Administradora',
                'estado' => 'activo',
                'ultimo' => 'Hoy 11:40',
            ],
            [
                'nombre' => 'Alejandra Riquelme',
                'email' => 'ariquelme@hsjmelipilla.cl',
                'rol' => 'Enfermera de turno',
                'estado' => 'activo',
                'ultimo' => 'Hoy 10:15',
            ],
            [
                'nombre' => 'Yamilet Maureira',
                'email' => 'ymaureira@hsjmelipilla.cl',
                'rol' => 'Operadora',
                'estado' => 'activo',
                'ultimo' => 'Hoy 14:12',
            ],
            [
                'nombre' => 'Carla Muñoz',
                'email' => 'cmunoz@hsjmelipilla.cl',
                'rol' => 'Operadora',
                'estado' => 'activo',
                'ultimo' => 'Ayer 18:02',
            ],
            [
                'nombre' => 'Patricia Vega',
                'email' => 'pvega@hsjmelipilla.cl',
                'rol' => 'Enfermera de turno',
                'estado' => 'inactivo',
                'ultimo' => '12/08/2026',
            ],
        ];

        return view('mockups.usuarios', [
            'usuario' => $this->usuarioSesion(),
            'usuarios' => $usuarios,
            'roles' => ['Administradora', 'Enfermera de turno', 'Operadora'],
            'resumen' => [
                'total' => count($usuarios),
                'activos' => collect($usuarios)->where('estado', 'activo')->count(),
                'inactivos' => collect($usuarios)->where('estado', 'inactivo')->count(),
                'admin' => collect($usuarios)->where('rol', 'Administradora')->count(),
            ],
        ]);
    }

    public function reportes(Request $request): View
    {
        $cajas = $this->cajas();
        $fases = $this->fases();
        $momento = now()->timezone('America/Santiago');
        $filas = array_map(fn (array $caja) => [
            'id' => $caja['id'],
            'servicio' => $caja['servicio'],
            'indice' => $caja['fase_idx'],
            'etapa' => $fases[$caja['fase_idx']] ?? '',
        ], $cajas);

        return view('mockups.reportes', [
            'usuario' => $this->usuarioSesion(),
            'falla' => $request->boolean('falla'),
            'fecha' => $momento->format('d/m/Y'),
            'hora' => $momento->format('H:i'),
            'fases' => $fases,
            'filas' => $filas,
        ]);
    }

    public function historialEntregas(Request $request): View
    {
        $entregas = [
            [
                'id' => 'ENT-241',
                'fecha' => '06/10/2026',
                'hora' => '11:40',
                'cuando' => 'hoy',
                'servicio' => 'Pabellón',
                'entrega' => 'Yamilet Maureira',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Daniela Soto',
                'recibe_rol' => 'Enfermera de pabellón',
                'cajas' => [
                    ['codigo' => 'SET-033', 'nombre' => 'Set de pabellón'],
                    ['codigo' => 'CAJA-091', 'nombre' => 'Caja instrumental'],
                ],
                'materiales' => ['Paquete de ropa'],
            ],
            [
                'id' => 'ENT-240',
                'fecha' => '06/10/2026',
                'hora' => '10:20',
                'cuando' => 'hoy',
                'servicio' => 'Dental',
                'entrega' => 'Carla Muñoz',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Daniela Soto',
                'recibe_rol' => 'Enfermera de dental',
                'cajas' => [
                    ['codigo' => 'SET-DEN-04', 'nombre' => 'Set dental'],
                ],
                'materiales' => ['Instrumental suelto'],
            ],
            [
                'id' => 'ENT-238',
                'fecha' => '06/10/2026',
                'hora' => '09:15',
                'cuando' => 'hoy',
                'servicio' => 'UCI',
                'entrega' => 'Carla Muñoz',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Alejandra Riquelme',
                'recibe_rol' => 'Enfermera de turno',
                'cajas' => [
                    ['codigo' => 'SET-055', 'nombre' => 'Set de UCI'],
                ],
                'materiales' => ['Material de curación'],
            ],
            [
                'id' => 'ENT-230',
                'fecha' => '05/10/2026',
                'hora' => '18:02',
                'cuando' => 'ayer',
                'servicio' => 'Urgencia',
                'entrega' => 'Yamilet Maureira',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Patricia Vega',
                'recibe_rol' => 'Enfermera de urgencia',
                'cajas' => [
                    ['codigo' => 'CAJA-118', 'nombre' => 'Caja de urgencia'],
                ],
                'materiales' => [],
            ],
            [
                'id' => 'ENT-226',
                'fecha' => '05/10/2026',
                'hora' => '16:20',
                'cuando' => 'ayer',
                'servicio' => 'Maternidad',
                'entrega' => 'Carla Muñoz',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Natalia Sánchez',
                'recibe_rol' => 'Jefatura de maternidad',
                'cajas' => [
                    ['codigo' => 'SET-007', 'nombre' => 'Set de maternidad'],
                ],
                'materiales' => ['Instrumental suelto'],
            ],
            [
                'id' => 'ENT-214',
                'fecha' => '04/10/2026',
                'hora' => '12:05',
                'cuando' => 'anterior',
                'servicio' => 'Curaciones',
                'entrega' => 'Yamilet Maureira',
                'entrega_rol' => 'Operadora',
                'recibe' => 'Daniela Soto',
                'recibe_rol' => 'Enfermera de curaciones',
                'cajas' => [
                    ['codigo' => 'CAJA-200', 'nombre' => 'Caja de curación'],
                ],
                'materiales' => ['Material de curación'],
            ],
        ];

        return view('mockups.historial-entregas', [
            'usuario' => $this->usuarioSesion(),
            'entregas' => $entregas,
            'servicios' => collect($entregas)->pluck('servicio')->unique()->values()->all(),
            'falla' => $request->boolean('falla'),
        ]);
    }

    public function custodia(Request $request): View
    {
        return view('mockups.custodia', [
            'usuario' => $this->usuarioSesion(),
            'falla' => $request->boolean('falla'),
            'cadenas' => [
                [
                    'caja' => 'SET-007',
                    'servicio' => 'Maternidad',
                    'estado' => 'En almacén',
                    'alerta' => false,
                    'cuando' => 'hoy',
                    'eventos' => [
                        ['hora' => '08:10', 'tipo' => 'Recepción', 'de' => 'Enf. Carla Muñoz', 'a' => 'Y. Maureira', 'nota' => 'Área sucia'],
                        ['hora' => '09:05', 'tipo' => 'Avance', 'de' => 'Y. Maureira', 'a' => '—', 'nota' => 'Recepción → Lavado'],
                        ['hora' => '10:20', 'tipo' => 'Avance', 'de' => 'A. Riquelme', 'a' => '—', 'nota' => 'Lavado → Preparación'],
                        ['hora' => '11:40', 'tipo' => 'Avance', 'de' => 'Y. Maureira', 'a' => '—', 'nota' => 'Preparación → Esterilización'],
                        ['hora' => '13:41', 'tipo' => 'Avance', 'de' => 'Y. Maureira', 'a' => '—', 'nota' => 'Esterilización → Almacén'],
                    ],
                ],
                [
                    'caja' => 'CAJA-118',
                    'servicio' => 'Urgencia',
                    'estado' => 'Incidencia',
                    'alerta' => true,
                    'cuando' => 'hoy',
                    'eventos' => [
                        ['hora' => '12:40', 'tipo' => 'Recepción', 'de' => 'Enf. Luis Perez', 'a' => 'A. Riquelme', 'nota' => 'Ingreso urgencia'],
                        ['hora' => '13:58', 'tipo' => 'Avance', 'de' => 'A. Riquelme', 'a' => '—', 'nota' => 'Recepción → Lavado'],
                        ['hora' => '14:05', 'tipo' => 'Alerta', 'de' => 'Sistema', 'a' => '—', 'nota' => 'Sin custodia al salir de lavado'],
                    ],
                ],
            ],
            'auditoria' => [
                ['fecha' => '06/10', 'hora' => '14:12', 'cuando' => 'hoy', 'actor' => 'Y. Maureira', 'accion' => 'Avanzó etapa', 'tipo' => 'etapa', 'objeto' => 'SET-042', 'detalle' => 'Preparación → Esterilización'],
                ['fecha' => '06/10', 'hora' => '13:58', 'cuando' => 'hoy', 'actor' => 'A. Riquelme', 'accion' => 'Avanzó etapa', 'tipo' => 'etapa', 'objeto' => 'CAJA-118', 'detalle' => 'Recepción → Lavado'],
                ['fecha' => '06/10', 'hora' => '13:41', 'cuando' => 'hoy', 'actor' => 'Y. Maureira', 'accion' => 'Avanzó etapa', 'tipo' => 'etapa', 'objeto' => 'SET-007', 'detalle' => 'Esterilización → Almacén'],
                ['fecha' => '06/10', 'hora' => '12:10', 'cuando' => 'hoy', 'actor' => 'N. Sánchez', 'accion' => 'Actualizó stock mínimo', 'tipo' => 'inventario', 'objeto' => 'SET-CES-02', 'detalle' => 'Mínimo 2 → 3'],
                ['fecha' => '06/10', 'hora' => '11:55', 'cuando' => 'hoy', 'actor' => 'N. Sánchez', 'accion' => 'Creó usuario', 'tipo' => 'usuarios', 'objeto' => 'C. Muñoz', 'detalle' => 'Rol Operadora'],
                ['fecha' => '06/10', 'hora' => '11:20', 'cuando' => 'hoy', 'actor' => 'Y. Maureira', 'accion' => 'Registró recepción', 'tipo' => 'etapa', 'objeto' => 'CAJA-091', 'detalle' => 'Desde Pabellón'],
                ['fecha' => '05/10', 'hora' => '10:45', 'cuando' => 'ayer', 'actor' => 'A. Riquelme', 'accion' => 'Registró entrega', 'tipo' => 'entrega', 'objeto' => 'SET-019', 'detalle' => 'Retira Enf. D. Soto · UCI'],
                ['fecha' => '01/10', 'hora' => '09:30', 'cuando' => 'mes', 'actor' => 'N. Sánchez', 'accion' => 'Desactivó usuario', 'tipo' => 'usuarios', 'objeto' => 'P. Vega', 'detalle' => 'Cuenta inactiva'],
            ],
        ]);
    }

    public function alertas(): View
    {
        $lista = [
            [
                'id' => 'AL-014',
                'nivel' => 'alta',
                'titulo' => 'SET-042 lleva 4h en esterilizacion',
                'detalle' => 'Pabellon · Autoclave 2 · iniciado 10:12',
                'tipo' => 'Retraso',
                'caja' => 'SET-042',
                'hora' => '14:12',
                'estado' => 'abierta',
            ],
            [
                'id' => 'AL-013',
                'nivel' => 'media',
                'titulo' => 'CAJA-118 sin custodia al salir de lavado',
                'detalle' => 'Urgencia · avance registrado sin receptor',
                'tipo' => 'Custodia',
                'caja' => 'CAJA-118',
                'hora' => '14:05',
                'estado' => 'abierta',
            ],
            [
                'id' => 'AL-012',
                'nivel' => 'media',
                'titulo' => 'Faltante en SET-007 (1 pinza)',
                'detalle' => 'Detectado en preparacion · Maternidad',
                'tipo' => 'Faltante',
                'caja' => 'SET-007',
                'hora' => '11:22',
                'estado' => 'abierta',
            ],
            [
                'id' => 'AL-011',
                'nivel' => 'baja',
                'titulo' => 'SET-CES-02 bajo stock mínimo',
                'detalle' => 'Stock 2 · mínimo 3 · Almacen A2',
                'tipo' => 'Inventario',
                'caja' => 'SET-CES-02',
                'hora' => '09:40',
                'estado' => 'abierta',
            ],
            [
                'id' => 'AL-010',
                'nivel' => 'alta',
                'titulo' => 'SET-UCI-03 stock critico',
                'detalle' => 'Stock 1 · mínimo 2 · UCI',
                'tipo' => 'Inventario',
                'caja' => 'SET-UCI-03',
                'hora' => '08:15',
                'estado' => 'abierta',
            ],
            [
                'id' => 'AL-009',
                'nivel' => 'media',
                'titulo' => 'Indicador clase 5 bajo mínimo',
                'detalle' => 'Bodega insumos · 12 / mín. 20',
                'tipo' => 'Inventario',
                'caja' => 'IND-CLASE5',
                'hora' => 'Ayer',
                'estado' => 'en_revision',
            ],
            [
                'id' => 'AL-008',
                'nivel' => 'baja',
                'titulo' => 'SET-019 demora en almacén >12h',
                'detalle' => 'Curaciones · lista sin retiro',
                'tipo' => 'Retraso',
                'caja' => 'SET-019',
                'hora' => 'Ayer',
                'estado' => 'resuelta',
            ],
        ];

        return view('mockups.alertas', [
            'usuario' => $this->usuarioSesion(),
            'alertas' => $lista,
            'resumen' => [
                'abiertas' => collect($lista)->where('estado', 'abierta')->count(),
                'alta' => collect($lista)->where('nivel', 'alta')->where('estado', '!=', 'resuelta')->count(),
                'inventario' => collect($lista)->where('tipo', 'Inventario')->where('estado', '!=', 'resuelta')->count(),
                'resueltas' => collect($lista)->where('estado', 'resuelta')->count(),
            ],
        ]);
    }

    public function cierreTurno(): View
    {
        return view('mockups.cierre-turno', [
            'usuario' => $this->usuarioSesion(),
            'turno' => [
                'fecha' => '15 sep 2026',
                'bloque' => 'Mañana · 08:00–16:00',
                'responsable' => 'Alejandra Riquelme',
                'relevo' => 'Patricia Vega',
                'operadoras' => 'Y. Maureira, C. Muñoz',
            ],
            'resumen' => [
                ['label' => 'Recepciones', 'value' => 14],
                ['label' => 'Entregas', 'value' => 11],
                ['label' => 'En proceso al cierre', 'value' => 7],
                ['label' => 'Alertas abiertas', 'value' => 3],
            ],
            'pendientes' => [
                'SET-042 sigue en esterilización (Autoclave 2) — revisar al inicio del próximo turno.',
                'CAJA-118 sin custodia al salir de lavado — validar con operadora de tarde.',
                'SET-CES-02 bajo mínimo (2 / 3) — avisar a administradora si no llega set de refuerzo.',
            ],
            'historial' => [
                ['fecha' => '14 sep', 'bloque' => 'Tarde 16:00–00:00', 'responsable' => 'Patricia Vega', 'estado' => 'Cerrado'],
                ['fecha' => '14 sep', 'bloque' => 'Mañana 08:00–16:00', 'responsable' => 'Alejandra Riquelme', 'estado' => 'Cerrado'],
                ['fecha' => '13 sep', 'bloque' => 'Tarde 16:00–00:00', 'responsable' => 'Alejandra Riquelme', 'estado' => 'Cerrado'],
            ],
        ]);
    }
}
