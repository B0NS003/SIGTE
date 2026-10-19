<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Seeder;

class CajaSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();
        $personas = [
            'alejandra' => $this->persona('alejandra.andrea@sigte.local'),
            'palacios' => $this->persona('alejandra.carmen@sigte.local'),
            'cynthia' => $this->persona('cynthia.mabel@sigte.local'),
            'camila' => $this->persona('camila.fernanda@sigte.local'),
            'felipe' => $this->persona('felipe.ignacio@sigte.local'),
        ];

        $demos = [
            [
                'codigo' => 'CAJA-044',
                'servicio' => 'Urgencia',
                'etapa' => Caja::ETAPA_RECEPCION,
                'ubicacion' => 'Area sucia',
                'estado' => 'Ingreso desde urgencia',
                'actividad' => null,
                'urgente' => true,
                'quien' => 'alejandra',
                'etapa_desde' => $ahora->copy()->subMinutes(12),
            ],
            [
                'codigo' => 'CAJA-091',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_RECEPCION,
                'ubicacion' => 'Area sucia',
                'estado' => 'Recibida, pendiente de lavado',
                'actividad' => null,
                'urgente' => false,
                'quien' => 'camila',
                'etapa_desde' => $ahora->copy()->subMinutes(28),
            ],
            [
                'codigo' => 'SET-018',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_LAVADO,
                'ubicacion' => 'Sala lavado',
                'estado' => 'En sala de lavado, sin ciclo',
                'actividad' => null,
                'urgente' => false,
                'quien' => 'cynthia',
                'etapa_desde' => $ahora->copy()->subMinutes(6),
            ],
            [
                'codigo' => 'CAJA-118',
                'servicio' => 'Urgencia',
                'etapa' => Caja::ETAPA_LAVADO,
                'ubicacion' => 'Lavadora 1',
                'estado' => 'En lavadora',
                'actividad' => 'Lavadora',
                'urgente' => false,
                'quien' => 'alejandra',
                'etapa_desde' => $ahora->copy()->subMinutes(40),
                'proceso_desde' => $ahora->copy()->subMinutes(22),
                'proceso_hasta' => $ahora->copy()->addMinutes(38),
            ],
            [
                'codigo' => 'CAJA-157',
                'servicio' => 'Maternidad',
                'etapa' => Caja::ETAPA_LAVADO,
                'ubicacion' => 'Sala lavado',
                'estado' => 'Ciclo de lavadora terminado',
                'actividad' => 'Lavadora',
                'urgente' => false,
                'quien' => 'camila',
                'etapa_desde' => $ahora->copy()->subMinutes(75),
                'proceso_desde' => $ahora->copy()->subMinutes(68),
                'proceso_hasta' => $ahora->copy()->subMinutes(8),
            ],
            [
                'codigo' => 'SET-061',
                'servicio' => 'UCI',
                'etapa' => Caja::ETAPA_LAVADO,
                'ubicacion' => 'Secado',
                'estado' => 'En secado',
                'actividad' => 'Secado',
                'urgente' => false,
                'quien' => 'palacios',
                'etapa_desde' => $ahora->copy()->subMinutes(30),
                'proceso_desde' => $ahora->copy()->subMinutes(10),
                'proceso_hasta' => $ahora->copy()->addMinutes(15),
            ],
            [
                'codigo' => 'SET-055',
                'servicio' => 'UCI',
                'etapa' => Caja::ETAPA_PREPARACION,
                'ubicacion' => 'Sala armado',
                'estado' => 'Inspeccion',
                'actividad' => 'Inspección',
                'urgente' => false,
                'quien' => 'felipe',
                'etapa_desde' => $ahora->copy()->subMinutes(18),
            ],
            [
                'codigo' => 'SET-090',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_PREPARACION,
                'ubicacion' => 'Sala armado',
                'estado' => 'Armado y rotulado',
                'actividad' => 'Armado y rotulado',
                'urgente' => false,
                'quien' => 'cynthia',
                'etapa_desde' => $ahora->copy()->subMinutes(35),
            ],
            [
                'codigo' => 'CAJA-012',
                'servicio' => 'Curaciones',
                'etapa' => Caja::ETAPA_PREPARACION,
                'ubicacion' => 'Sala armado',
                'estado' => 'Reconteo',
                'actividad' => 'Reconteo',
                'urgente' => false,
                'quien' => 'palacios',
                'etapa_desde' => $ahora->copy()->subMinutes(50),
            ],
            [
                'codigo' => 'SET-128',
                'servicio' => 'Urgencia',
                'etapa' => Caja::ETAPA_ESTERILIZACION,
                'ubicacion' => 'Autoclave 1',
                'estado' => 'Ciclo de vapor en curso',
                'actividad' => 'Ciclo de vapor',
                'urgente' => false,
                'quien' => 'felipe',
                'etapa_desde' => $ahora->copy()->subMinutes(45),
            ],
            [
                'codigo' => 'SET-042',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_ESTERILIZACION,
                'ubicacion' => 'Autoclave 2',
                'estado' => 'Espera para abrir la puerta',
                'actividad' => 'Espera para abrir',
                'urgente' => true,
                'quien' => 'alejandra',
                'etapa_desde' => $ahora->copy()->subMinutes(70),
                'proceso_desde' => $ahora->copy()->subMinutes(7),
                'proceso_hasta' => $ahora->copy()->addMinutes(13),
            ],
            [
                'codigo' => 'CAJA-073',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_ALMACEN,
                'ubicacion' => 'Almacen esteril A1',
                'estado' => 'Enfriando material chico',
                'actividad' => 'Material chico',
                'urgente' => false,
                'quien' => 'cynthia',
                'etapa_desde' => $ahora->copy()->subMinutes(25),
                'proceso_desde' => $ahora->copy()->subMinutes(12),
                'proceso_hasta' => $ahora->copy()->addMinutes(18),
            ],
            [
                'codigo' => 'SET-033',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_ALMACEN,
                'ubicacion' => 'Almacen esteril A2',
                'estado' => 'Enfriando contenedor',
                'actividad' => 'Contenedor',
                'urgente' => true,
                'quien' => 'palacios',
                'etapa_desde' => $ahora->copy()->subMinutes(40),
                'proceso_desde' => $ahora->copy()->subMinutes(20),
                'proceso_hasta' => $ahora->copy()->addMinutes(40),
            ],
            [
                'codigo' => 'SET-007',
                'servicio' => 'Maternidad',
                'etapa' => Caja::ETAPA_ALMACEN,
                'ubicacion' => 'Almacen esteril B1',
                'estado' => 'Lista para entregar',
                'actividad' => 'Material chico',
                'urgente' => false,
                'quien' => 'camila',
                'etapa_desde' => $ahora->copy()->subHours(2),
                'proceso_desde' => $ahora->copy()->subMinutes(90),
                'proceso_hasta' => $ahora->copy()->subMinutes(60),
            ],
            [
                'codigo' => 'CAJA-200',
                'servicio' => 'Curaciones',
                'etapa' => Caja::ETAPA_ALMACEN,
                'ubicacion' => 'Almacen esteril B1',
                'estado' => 'Lista en almacen esteril',
                'actividad' => null,
                'urgente' => false,
                'quien' => 'felipe',
                'etapa_desde' => $ahora->copy()->subMinutes(70),
            ],
            [
                'codigo' => 'CAJA-221',
                'servicio' => 'Maternidad',
                'etapa' => Caja::ETAPA_ENTREGA,
                'ubicacion' => 'Ventanilla de entrega',
                'estado' => 'Esperando retiro de maternidad',
                'actividad' => null,
                'urgente' => false,
                'quien' => 'alejandra',
                'etapa_desde' => $ahora->copy()->subMinutes(25),
            ],
            [
                'codigo' => 'SET-015',
                'servicio' => 'UCI',
                'etapa' => Caja::ETAPA_ENTREGA,
                'ubicacion' => 'Ventanilla de entrega',
                'estado' => 'Esperando retiro de UCI',
                'actividad' => null,
                'urgente' => false,
                'quien' => 'cynthia',
                'etapa_desde' => $ahora->copy()->subMinutes(40),
            ],
        ];

        foreach ($demos as $demo) {
            $quien = $personas[$demo['quien']] ?? null;
            unset($demo['quien']);
            $demo['responsable_id'] = $quien['user_id'] ?? null;
            $demo['servicio_id'] = Servicio::idPorNombre($demo['servicio']);
            unset($demo['servicio']);
            $demo['proceso_desde'] = $demo['proceso_desde'] ?? null;
            $demo['proceso_hasta'] = $demo['proceso_hasta'] ?? null;
            $demo['entregado_at'] = null;

            Caja::query()->updateOrCreate(
                ['codigo' => $demo['codigo']],
                $demo
            );
        }
    }

    /** @return array{user_id: int}|null */
    private function persona(string $email): ?array
    {
        $user = User::query()->where('email', $email)->first();
        if ($user === null) {
            return null;
        }

        return [
            'user_id' => $user->id,
        ];
    }
}
