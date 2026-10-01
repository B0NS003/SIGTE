<?php

namespace App\Support;

use App\Models\Rol;
use App\Models\User;

class AccesoPorRol
{
    /**
     * Rutas del mockup y roles que pueden abrirlas.
     * El panel propio se controla aparte, porque la URL incluye el rol.
     *
     * @var array<string, list<string>>
     */
    private const RUTAS = [
        'mockups.recepcion' => [Rol::OPERADOR],
        'mockups.avanzar' => [Rol::OPERADOR],
        'mockups.entrega' => [Rol::OPERADOR],
        'mockups.catalogo' => [Rol::OPERADOR, Rol::ENFERMERA, Rol::ADMINISTRADORA],
        'mockups.inventario' => [Rol::OPERADOR, Rol::ENFERMERA, Rol::ADMINISTRADORA],
        'mockups.alertas' => [Rol::ENFERMERA, Rol::ADMINISTRADORA],
        'mockups.cierre_turno' => [Rol::ENFERMERA],
        'mockups.reportes' => [Rol::ADMINISTRADORA],
        'mockups.custodia' => [Rol::ADMINISTRADORA],
        'mockups.usuarios' => [Rol::ADMINISTRADORA],
    ];

    public static function puedeVerPanel(User $user, string $rolEnUrl): bool
    {
        $rol = $user->nombreRol();

        if ($rol === null) {
            return false;
        }

        if ($rolEnUrl === $rol) {
            return true;
        }

        return $rolEnUrl === Rol::OPERADOR
            && in_array($rol, [Rol::ENFERMERA, Rol::ADMINISTRADORA], true);
    }

    public static function puedeAbrirRuta(User $user, ?string $ruta): bool
    {
        if ($ruta === null || ! isset(self::RUTAS[$ruta])) {
            return false;
        }

        return in_array($user->nombreRol(), self::RUTAS[$ruta], true);
    }

    /**
     * @return list<array{titulo: string, enlaces: list<array{url: string, texto: string, activo: bool, badge: ?string}>}>
     */
    public static function menu(User $user): array
    {
        $rol = $user->nombreRol();
        $grupos = [];

        foreach (self::enlaces() as $enlace) {
            if (! in_array($rol, $enlace['roles'], true)) {
                continue;
            }

            $params = $enlace['params'];
            if (($enlace['propio'] ?? false) === true) {
                $params = ['rol' => $rol];
            }

            $titulo = $enlace['grupos'][$rol] ?? $enlace['grupo'];
            $grupos[$titulo][] = [
                'url' => route($enlace['ruta'], $params),
                'texto' => self::texto($enlace, $rol),
                'activo' => self::activo($enlace, $rol),
                'badge' => $enlace['badge'] ?? null,
            ];
        }

        $menu = [];
        foreach ($grupos as $titulo => $items) {
            $menu[] = ['titulo' => $titulo, 'enlaces' => $items];
        }

        return $menu;
    }

    /**
     * @return list<array{grupo: string, ruta: string, texto: string, roles: list<string>, params: array<string, string>, propio?: bool, textos?: array<string, string>, badge?: string}>
     */
    private static function enlaces(): array
    {
        $operacion = [Rol::OPERADOR];
        $consulta = [Rol::OPERADOR, Rol::ENFERMERA];

        return [
            [
                'grupo' => 'Mi trabajo',
                'grupos' => [Rol::ADMINISTRADORA => 'Gestión'],
                'ruta' => 'mockups.panel',
                'texto' => 'Inicio',
                'propio' => true,
                'params' => [],
                'roles' => [Rol::OPERADOR, Rol::ENFERMERA, Rol::ADMINISTRADORA],
                'textos' => [
                    Rol::OPERADOR => 'Flujo de cajas',
                    Rol::ENFERMERA => 'Resumen turno',
                    Rol::ADMINISTRADORA => 'Resumen',
                ],
            ],
            [
                'grupo' => 'Mi trabajo',
                'ruta' => 'mockups.recepcion',
                'texto' => 'Recepción',
                'params' => [],
                'roles' => $operacion,
            ],
            [
                'grupo' => 'Mi trabajo',
                'ruta' => 'mockups.avanzar',
                'texto' => 'Avanzar etapa',
                'params' => [],
                'roles' => $operacion,
            ],
            [
                'grupo' => 'Mi trabajo',
                'ruta' => 'mockups.entrega',
                'texto' => 'Entrega',
                'params' => [],
                'roles' => $operacion,
            ],
            [
                'grupo' => 'Turno',
                'ruta' => 'mockups.alertas',
                'texto' => 'Alertas',
                'params' => [],
                'roles' => [Rol::ENFERMERA],
                'badge' => '3',
            ],
            [
                'grupo' => 'Turno',
                'ruta' => 'mockups.cierre_turno',
                'texto' => 'Cierre de turno',
                'params' => [],
                'roles' => [Rol::ENFERMERA],
            ],
            [
                'grupo' => 'Consulta',
                'ruta' => 'mockups.panel',
                'texto' => 'Flujo de cajas',
                'params' => ['rol' => Rol::OPERADOR],
                'roles' => [Rol::ENFERMERA],
            ],
            [
                'grupo' => 'Consulta',
                'ruta' => 'mockups.catalogo',
                'texto' => 'Catálogo',
                'params' => [],
                'roles' => $consulta,
            ],
            [
                'grupo' => 'Consulta',
                'ruta' => 'mockups.inventario',
                'texto' => 'Inventario',
                'params' => [],
                'roles' => $consulta,
            ],
            [
                'grupo' => 'Gestión',
                'ruta' => 'mockups.catalogo',
                'texto' => 'Catálogo',
                'params' => [],
                'roles' => [Rol::ADMINISTRADORA],
            ],
            [
                'grupo' => 'Gestión',
                'ruta' => 'mockups.inventario',
                'texto' => 'Inventario',
                'params' => [],
                'roles' => [Rol::ADMINISTRADORA],
            ],
            [
                'grupo' => 'Gestión',
                'ruta' => 'mockups.usuarios',
                'texto' => 'Usuarios',
                'params' => [],
                'roles' => [Rol::ADMINISTRADORA],
            ],
            [
                'grupo' => 'Gestión',
                'ruta' => 'mockups.reportes',
                'texto' => 'Reportes',
                'params' => [],
                'roles' => [Rol::ADMINISTRADORA],
            ],
            [
                'grupo' => 'Gestión',
                'ruta' => 'mockups.custodia',
                'texto' => 'Custodia',
                'params' => [],
                'roles' => [Rol::ADMINISTRADORA],
            ],
            [
                'grupo' => 'Gestión',
                'ruta' => 'mockups.alertas',
                'texto' => 'Alertas',
                'params' => [],
                'roles' => [Rol::ADMINISTRADORA],
                'badge' => '3',
            ],
            [
                'grupo' => 'Gestión',
                'ruta' => 'mockups.panel',
                'texto' => 'Ver flujo operadora',
                'params' => ['rol' => Rol::OPERADOR],
                'roles' => [Rol::ADMINISTRADORA],
            ],
        ];
    }

    /**
     * @param  array{texto: string, textos?: array<string, string>}  $enlace
     */
    private static function texto(array $enlace, ?string $rol): string
    {
        return $enlace['textos'][$rol] ?? $enlace['texto'];
    }

    /**
     * @param  array{ruta: string, propio?: bool, params: array<string, string>}  $enlace
     */
    private static function activo(array $enlace, ?string $rol): bool
    {
        if (! request()->routeIs($enlace['ruta'])) {
            return false;
        }

        if ($enlace['ruta'] !== 'mockups.panel') {
            return true;
        }

        $rolUrl = request()->route('rol');
        $rolEsperado = ($enlace['propio'] ?? false) ? $rol : ($enlace['params']['rol'] ?? null);

        return $rolUrl === $rolEsperado;
    }
}
