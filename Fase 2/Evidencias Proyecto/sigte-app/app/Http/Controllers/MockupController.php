<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MockupController extends Controller
{
    private function fases(): array
    {
        return ['Recepcion', 'Lavado', 'Preparacion', 'Esterilizacion', 'Almacen', 'Entrega'];
    }

    private function cajas(): array
    {
        return [
            [
                'id' => 'SET-042',
                'servicio' => 'Pabellon',
                'fase_idx' => 3,
                'estado' => 'En autoclave 2',
                'operadora' => 'Y. Maureira',
                'hora' => '14:12',
                'urgente' => true,
            ],
            [
                'id' => 'CAJA-118',
                'servicio' => 'Urgencia',
                'fase_idx' => 1,
                'estado' => 'En lavado (ciclo ~1h)',
                'operadora' => 'A. Riquelme',
                'hora' => '13:58',
                'urgente' => false,
            ],
            [
                'id' => 'SET-007',
                'servicio' => 'Maternidad',
                'fase_idx' => 4,
                'estado' => 'Lista en almacen esteril',
                'operadora' => 'Y. Maureira',
                'hora' => '13:41',
                'urgente' => false,
            ],
            [
                'id' => 'CAJA-091',
                'servicio' => 'Pabellon',
                'fase_idx' => 0,
                'estado' => 'Area sucia · ficha de servicio',
                'operadora' => 'A. Riquelme',
                'hora' => '13:20',
                'urgente' => false,
            ],
            [
                'id' => 'SET-055',
                'servicio' => 'UCI',
                'fase_idx' => 2,
                'estado' => 'Armado / reconteo',
                'operadora' => 'Y. Maureira',
                'hora' => '12:55',
                'urgente' => false,
            ],
            [
                'id' => 'SET-033',
                'servicio' => 'Pabellon',
                'fase_idx' => 4,
                'estado' => 'Lista en almacen esteril',
                'operadora' => 'Y. Maureira',
                'hora' => '12:10',
                'urgente' => true,
            ],
            [
                'id' => 'CAJA-200',
                'servicio' => 'Curaciones',
                'fase_idx' => 4,
                'estado' => 'Lista en almacen esteril',
                'operadora' => 'A. Riquelme',
                'hora' => '11:48',
                'urgente' => false,
            ],
        ];
    }


    private function catalogoItems(): array
    {
        return [
            [
                'codigo' => 'SET-LAP-01',
                'nombre' => 'Set laparoscopia basico',
                'tipo' => 'Set quirurgico',
                'piezas' => 18,
                'servicio' => 'Pabellon',
                'contenido' => 'Trocares, pinzas, tijera, aspiracion',
            ],
            [
                'codigo' => 'SET-CES-02',
                'nombre' => 'Set cesarea',
                'tipo' => 'Set quirurgico',
                'piezas' => 24,
                'servicio' => 'Maternidad',
                'contenido' => 'Bisturis, pinzas, retractores, portaagujas',
            ],
            [
                'codigo' => 'CAJ-CUR-10',
                'nombre' => 'Caja curacion general',
                'tipo' => 'Caja de curacion',
                'piezas' => 12,
                'servicio' => 'Curaciones',
                'contenido' => 'Pinzas anatomicas, tijera, riñonera',
            ],
            [
                'codigo' => 'SET-UCI-03',
                'nombre' => 'Set via aerea UCI',
                'tipo' => 'Set quirurgico',
                'piezas' => 9,
                'servicio' => 'UCI',
                'contenido' => 'Laringoscopio, pinzas Magill, guia',
            ],
            [
                'codigo' => 'CNT-EST-01',
                'nombre' => 'Contenedor rigidó 1/1',
                'tipo' => 'Contenedor',
                'piezas' => 1,
                'servicio' => 'General',
                'contenido' => 'Contenedor con filtro e indicadores',
            ],
            [
                'codigo' => 'PKG-GM-05',
                'nombre' => 'Paquete grado medico pequeño',
                'tipo' => 'Paquete grado medico',
                'piezas' => 1,
                'servicio' => 'Urgencia',
                'contenido' => 'Instrumental suelto empacado',
            ],
        ];
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


    public function login(): View
    {
        return view('mockups.login');
    }

    public function panel(string $rol): View
    {
        $roles = [
            'administradora' => [
                'nombre' => 'Natalia Sanchez',
                'rol' => 'Administradora',
                'vista' => 'mockups.panel-admin',
            ],
            'enfermera' => [
                'nombre' => 'Alejandra Riquelme',
                'rol' => 'Enfermera de turno',
                'vista' => 'mockups.panel-enfermera',
            ],
            'operador' => [
                'nombre' => 'Yamilet Maureira',
                'rol' => 'Operadora',
                'vista' => 'mockups.panel-operador',
            ],
        ];

        $usuario = $roles[$rol];

        return view($usuario['vista'], [
            'usuario' => $usuario,
            'rol_key' => $rol,
            'fases' => $this->fases(),
            'cajas' => $this->cajas(),
            'kpis' => [
                ['label' => 'En proceso', 'value' => 28, 'hint' => 'Cajas/sets activos', 'tone' => ''],
                ['label' => 'Listas para entrega', 'value' => 12, 'hint' => 'En almacen esteril', 'tone' => 'ok'],
                ['label' => 'Entregadas hoy', 'value' => 19, 'hint' => 'Con custodia', 'tone' => ''],
                ['label' => 'Requieren atencion', 'value' => 3, 'hint' => 'Retraso o faltante', 'tone' => 'warn'],
            ],
            'alertas' => [
                ['nivel' => 'alta', 'titulo' => 'SET-042 lleva 4h en esterilizacion', 'detalle' => 'Pabellon · Autoclave 2'],
                ['nivel' => 'media', 'titulo' => 'CAJA-118 sin custodia al salir de lavado', 'detalle' => 'Urgencia'],
                ['nivel' => 'media', 'titulo' => 'Faltante en SET-007 (1 pinza)', 'detalle' => 'Detectado en preparacion'],
            ],
        ]);
    }

    public function recepcion(): View
    {
        return view('mockups.recepcion', [
            'usuario' => [
                'nombre' => 'Yamilet Maureira',
                'rol' => 'Operadora',
            ],
            'servicios' => ['Pabellon', 'Urgencia', 'Maternidad', 'UCI', 'Curaciones', 'Otro servicio'],
            'tipos' => ['Set quirurgico', 'Caja de curacion', 'Contenedor', 'Paquete grado medico'],
        ]);
    }

    public function avanzar(Request $request): View
    {
        $fases = $this->fases();
        $cajas = $this->cajas();
        $selectedId = $request->query('caja', 'CAJA-118');
        $caja = collect($cajas)->firstWhere('id', $selectedId) ?? $cajas[1];
        $idx = $caja['fase_idx'];
        $siguiente = $idx < count($fases) - 1 ? $fases[$idx + 1] : null;

        return view('mockups.avanzar', [
            'usuario' => [
                'nombre' => 'Yamilet Maureira',
                'rol' => 'Operadora',
            ],
            'fases' => $fases,
            'cajas' => $cajas,
            'caja' => $caja,
            'fase_actual' => $fases[$idx],
            'fase_siguiente' => $siguiente,
        ]);
    }

    public function entrega(Request $request): View
    {
        $fases = $this->fases();
        $listas = collect($this->cajas())->where('fase_idx', 4)->values()->all();
        $selectedId = $request->query('caja', $listas[0]['id'] ?? 'SET-007');
        $caja = collect($listas)->firstWhere('id', $selectedId) ?? ($listas[0] ?? null);

        return view('mockups.entrega', [
            'usuario' => [
                'nombre' => 'Yamilet Maureira',
                'rol' => 'Operadora',
            ],
            'fases' => $fases,
            'listas' => $listas,
            'caja' => $caja,
            'servicios' => ['Pabellon', 'Urgencia', 'Maternidad', 'UCI', 'Curaciones', 'Otro servicio'],
        ]);
    }

    public function catalogo(): View
    {
        return view('mockups.catalogo', [
            'usuario' => [
                'nombre' => 'Yamilet Maureira',
                'rol' => 'Operadora',
            ],
            'items' => $this->catalogoItems(),
        ]);
    }

    public function inventario(): View
    {
        $items = $this->inventarioItems();
        $sala = request()->query('sala', 'esteril');
        if (! in_array($sala, ['lavado', 'armado', 'esteril'], true)) {
            $sala = 'esteril';
        }
        $filtrados = collect($items)->where('sala', $sala)->values()->all();
        $alertas = collect($filtrados)->whereIn('estado', ['bajo', 'critico'])->count();

        return view('mockups.inventario', [
            'usuario' => [
                'nombre' => 'Yamilet Maureira',
                'rol' => 'Operadora',
            ],
            'sala' => $sala,
            'salas' => [
                'lavado' => 'Sala lavado',
                'armado' => 'Sala armado',
                'esteril' => 'Material estéril',
            ],
            'items' => $filtrados,
            'resumen' => [
                'tipos' => count($filtrados),
                'bajo_minimo' => $alertas,
                'en_almacen' => collect($filtrados)->sum('stock'),
                'en_proceso' => collect($filtrados)->sum('en_proceso'),
            ],
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
            'usuario' => [
                'nombre' => 'Natalia Sanchez',
                'rol' => 'Administradora',
            ],
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

    public function reportes(): View
    {
        return view('mockups.reportes', [
            'usuario' => [
                'nombre' => 'Natalia Sanchez',
                'rol' => 'Administradora',
            ],
            'periodo' => '15–21 sep 2026',
            'kpis' => [
                ['label' => 'Recepciones', 'value' => 86, 'hint' => 'Ingresos al ciclo', 'tone' => ''],
                ['label' => 'Esterilizaciones', 'value' => 71, 'hint' => 'Ciclos cerrados OK', 'tone' => 'ok'],
                ['label' => 'Entregas', 'value' => 64, 'hint' => 'Con custodia', 'tone' => ''],
                ['label' => 'Incidencias', 'value' => 7, 'hint' => 'Faltantes / retrasos', 'tone' => 'warn'],
            ],
            'por_servicio' => [
                ['servicio' => 'Pabellon', 'recepciones' => 28, 'entregas' => 22, 'incidencias' => 3],
                ['servicio' => 'Urgencia', 'recepciones' => 18, 'entregas' => 15, 'incidencias' => 2],
                ['servicio' => 'Maternidad', 'recepciones' => 16, 'entregas' => 14, 'incidencias' => 1],
                ['servicio' => 'UCI', 'recepciones' => 12, 'entregas' => 9, 'incidencias' => 1],
                ['servicio' => 'Curaciones', 'recepciones' => 12, 'entregas' => 4, 'incidencias' => 0],
            ],
            'por_fase' => [
                ['fase' => 'Recepcion', 'promedio_h' => 0.4, 'max_h' => 1.2],
                ['fase' => 'Lavado', 'promedio_h' => 1.1, 'max_h' => 2.5],
                ['fase' => 'Preparacion', 'promedio_h' => 0.9, 'max_h' => 2.0],
                ['fase' => 'Esterilizacion', 'promedio_h' => 2.4, 'max_h' => 4.0],
                ['fase' => 'Almacen', 'promedio_h' => 6.5, 'max_h' => 18.0],
                ['fase' => 'Entrega', 'promedio_h' => 0.3, 'max_h' => 1.0],
            ],
            'top_sets' => [
                ['codigo' => 'SET-CES-02', 'nombre' => 'Set cesarea', 'ciclos' => 14],
                ['codigo' => 'SET-LAP-01', 'nombre' => 'Set laparoscopia basico', 'ciclos' => 11],
                ['codigo' => 'CAJ-CUR-10', 'nombre' => 'Caja curacion general', 'ciclos' => 9],
                ['codigo' => 'SET-UCI-03', 'nombre' => 'Set via aerea UCI', 'ciclos' => 6],
            ],
        ]);
    }

    public function custodia(): View
    {
        return view('mockups.custodia', [
            'usuario' => [
                'nombre' => 'Natalia Sanchez',
                'rol' => 'Administradora',
            ],
            'cadenas' => [
                [
                    'caja' => 'SET-007',
                    'servicio' => 'Maternidad',
                    'estado' => 'En almacen',
                    'eventos' => [
                        ['hora' => '08:10', 'tipo' => 'Recepcion', 'de' => 'Enf. Carla Muñoz', 'a' => 'Y. Maureira', 'nota' => 'Area sucia'],
                        ['hora' => '09:05', 'tipo' => 'Avance', 'de' => 'Y. Maureira', 'a' => '—', 'nota' => 'Recepcion → Lavado'],
                        ['hora' => '10:20', 'tipo' => 'Avance', 'de' => 'A. Riquelme', 'a' => '—', 'nota' => 'Lavado → Preparacion'],
                        ['hora' => '11:40', 'tipo' => 'Avance', 'de' => 'Y. Maureira', 'a' => '—', 'nota' => 'Preparacion → Esterilizacion'],
                        ['hora' => '13:41', 'tipo' => 'Avance', 'de' => 'Y. Maureira', 'a' => '—', 'nota' => 'Esterilizacion → Almacen'],
                    ],
                ],
                [
                    'caja' => 'CAJA-118',
                    'servicio' => 'Urgencia',
                    'estado' => 'Incidencia',
                    'eventos' => [
                        ['hora' => '12:40', 'tipo' => 'Recepcion', 'de' => 'Enf. Luis Perez', 'a' => 'A. Riquelme', 'nota' => 'Ingreso urgencia'],
                        ['hora' => '13:58', 'tipo' => 'Avance', 'de' => 'A. Riquelme', 'a' => '—', 'nota' => 'Recepcion → Lavado'],
                        ['hora' => '14:05', 'tipo' => 'Alerta', 'de' => 'Sistema', 'a' => '—', 'nota' => 'Sin custodia al salir de lavado'],
                    ],
                ],
            ],
            'auditoria' => [
                ['hora' => '14:12', 'actor' => 'Y. Maureira', 'accion' => 'Avanzó etapa', 'objeto' => 'SET-042', 'detalle' => 'Preparacion → Esterilizacion'],
                ['hora' => '13:58', 'actor' => 'A. Riquelme', 'accion' => 'Avanzó etapa', 'objeto' => 'CAJA-118', 'detalle' => 'Recepcion → Lavado'],
                ['hora' => '13:41', 'actor' => 'Y. Maureira', 'accion' => 'Avanzó etapa', 'objeto' => 'SET-007', 'detalle' => 'Esterilizacion → Almacen'],
                ['hora' => '12:10', 'actor' => 'N. Sanchez', 'accion' => 'Actualizó stock mínimo', 'objeto' => 'SET-CES-02', 'detalle' => 'Mínimo 2 → 3'],
                ['hora' => '11:55', 'actor' => 'N. Sanchez', 'accion' => 'Creó usuario', 'objeto' => 'C. Muñoz', 'detalle' => 'Rol Operadora'],
                ['hora' => '11:20', 'actor' => 'Y. Maureira', 'accion' => 'Registró recepción', 'objeto' => 'CAJA-091', 'detalle' => 'Desde Pabellon'],
                ['hora' => '10:45', 'actor' => 'A. Riquelme', 'accion' => 'Registró entrega', 'objeto' => 'SET-019', 'detalle' => 'Retira Enf. D. Soto · UCI'],
                ['hora' => '09:30', 'actor' => 'N. Sanchez', 'accion' => 'Desactivó usuario', 'objeto' => 'P. Vega', 'detalle' => 'Cuenta inactiva'],
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
            'usuario' => [
                'nombre' => 'Natalia Sanchez',
                'rol' => 'Administradora',
            ],
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
            'usuario' => [
                'nombre' => 'Alejandra Riquelme',
                'rol' => 'Enfermera de turno',
            ],
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
