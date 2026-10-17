<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ConsultaDeCajas;
use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\Caja;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    use ConsultaDeCajas;
    use ResuelveUsuario;

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
        return view('admin.usuarios', [
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
        return view('admin.reportes', [
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
        return view('admin.historial', [
            'usuario' => $this->usuarioSesion(),
            'entregas' => $entregas,
            'servicios' => collect($entregas)->pluck('servicio')->unique()->values()->all(),
            'falla' => $request->boolean('falla'),
        ]);
    }
    public function custodia(Request $request): View
    {
        return view('admin.custodia', [
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
        return view('admin.alertas', [
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
}
