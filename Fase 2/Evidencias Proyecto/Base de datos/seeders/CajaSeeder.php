<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\User;
use Illuminate\Database\Seeder;

class CajaSeeder extends Seeder
{
    public function run(): void
    {
        $operador = User::query()->where('email', 'operador@sigte.local')->first();
        $enfermera = User::query()->where('email', 'enfermera@sigte.local')->first();

        $demos = [
            [
                'codigo' => 'SET-042',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_ESTERILIZACION,
                'ubicacion' => 'Autoclave 2',
                'estado' => 'Espera para abrir la puerta',
                'actividad' => 'Espera para abrir',
                'urgente' => true,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subHours(4),
                'proceso_desde' => now()->subMinutes(8),
                'proceso_hasta' => now()->addMinutes(12),
            ],
            [
                'codigo' => 'CAJA-118',
                'servicio' => 'Urgencia',
                'etapa' => Caja::ETAPA_LAVADO,
                'ubicacion' => 'Sala lavado',
                'estado' => 'En lavadora',
                'actividad' => 'Lavadora',
                'urgente' => false,
                'responsable_id' => $enfermera?->id,
                'etapa_desde' => now()->subMinutes(55),
                'proceso_desde' => now()->subMinutes(20),
                'proceso_hasta' => now()->addMinutes(40),
            ],
            [
                'codigo' => 'SET-007',
                'servicio' => 'Maternidad',
                'etapa' => Caja::ETAPA_ALMACEN,
                'ubicacion' => 'Almacen esteril B1',
                'estado' => 'Lista en almacen esteril',
                'urgente' => false,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subHours(2),
            ],
            [
                'codigo' => 'CAJA-091',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_RECEPCION,
                'ubicacion' => 'Area sucia',
                'estado' => 'Area sucia · ficha de servicio',
                'urgente' => false,
                'responsable_id' => $enfermera?->id,
                'etapa_desde' => now()->subMinutes(80),
            ],
            [
                'codigo' => 'SET-055',
                'servicio' => 'UCI',
                'etapa' => Caja::ETAPA_PREPARACION,
                'ubicacion' => 'Sala armado',
                'estado' => 'Armado / reconteo',
                'urgente' => false,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subMinutes(40),
            ],
            [
                'codigo' => 'SET-033',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_ALMACEN,
                'ubicacion' => 'Almacen esteril A2',
                'estado' => 'Lista en almacen esteril',
                'urgente' => true,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subHours(3),
            ],
            [
                'codigo' => 'CAJA-200',
                'servicio' => 'Curaciones',
                'etapa' => Caja::ETAPA_ALMACEN,
                'ubicacion' => 'Almacen esteril B1',
                'estado' => 'Lista en almacen esteril',
                'urgente' => false,
                'responsable_id' => $enfermera?->id,
                'etapa_desde' => now()->subHours(1),
            ],
            [
                'codigo' => 'SET-018',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_RECEPCION,
                'ubicacion' => 'Area sucia',
                'estado' => 'Recibida, pendiente de lavado',
                'urgente' => false,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subMinutes(25),
            ],
            [
                'codigo' => 'CAJA-044',
                'servicio' => 'Urgencia',
                'etapa' => Caja::ETAPA_RECEPCION,
                'ubicacion' => 'Area sucia',
                'estado' => 'Ingreso desde pabellon de urgencia',
                'urgente' => true,
                'responsable_id' => $enfermera?->id,
                'etapa_desde' => now()->subMinutes(15),
            ],
            [
                'codigo' => 'SET-061',
                'servicio' => 'UCI',
                'etapa' => Caja::ETAPA_LAVADO,
                'ubicacion' => 'Sala lavado',
                'estado' => 'En secado',
                'actividad' => 'Secado',
                'urgente' => false,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subMinutes(35),
                'proceso_desde' => now()->subMinutes(10),
                'proceso_hasta' => now()->addMinutes(20),
            ],
            [
                'codigo' => 'CAJA-157',
                'servicio' => 'Maternidad',
                'etapa' => Caja::ETAPA_LAVADO,
                'ubicacion' => 'Sala lavado',
                'estado' => 'Ciclo de lavadora terminado',
                'actividad' => 'Lavadora',
                'urgente' => false,
                'responsable_id' => $enfermera?->id,
                'etapa_desde' => now()->subMinutes(70),
                'proceso_desde' => now()->subMinutes(65),
                'proceso_hasta' => now()->subMinutes(5),
            ],
            [
                'codigo' => 'SET-090',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_PREPARACION,
                'ubicacion' => 'Sala armado',
                'estado' => 'Inspeccion y armado',
                'actividad' => 'Inspeccion',
                'urgente' => false,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subMinutes(20),
            ],
            [
                'codigo' => 'CAJA-012',
                'servicio' => 'Curaciones',
                'etapa' => Caja::ETAPA_PREPARACION,
                'ubicacion' => 'Sala armado',
                'estado' => 'Empaque',
                'actividad' => 'Empaque',
                'urgente' => false,
                'responsable_id' => $enfermera?->id,
                'etapa_desde' => now()->subMinutes(50),
            ],
            [
                'codigo' => 'SET-128',
                'servicio' => 'Urgencia',
                'etapa' => Caja::ETAPA_ESTERILIZACION,
                'ubicacion' => 'Autoclave 1',
                'estado' => 'En autoclave 1',
                'actividad' => 'Ciclo de vapor',
                'urgente' => false,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subHours(2),
            ],
            [
                'codigo' => 'CAJA-073',
                'servicio' => 'Pabellon',
                'etapa' => Caja::ETAPA_ALMACEN,
                'ubicacion' => 'Almacen esteril A1',
                'estado' => 'Enfriando material chico',
                'actividad' => 'Material chico',
                'urgente' => false,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subHours(5),
                'proceso_desde' => now()->subMinutes(15),
                'proceso_hasta' => now()->addMinutes(15),
            ],
            [
                'codigo' => 'SET-015',
                'servicio' => 'UCI',
                'etapa' => Caja::ETAPA_ENTREGA,
                'ubicacion' => 'Ventanilla de entrega',
                'estado' => 'Entregada a UCI',
                'urgente' => false,
                'responsable_id' => $enfermera?->id,
                'etapa_desde' => now()->subMinutes(30),
            ],
            [
                'codigo' => 'CAJA-221',
                'servicio' => 'Maternidad',
                'etapa' => Caja::ETAPA_ENTREGA,
                'ubicacion' => 'Ventanilla de entrega',
                'estado' => 'Entregada a maternidad',
                'urgente' => false,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subHours(1),
            ],
        ];

        foreach ($demos as $demo) {
            Caja::query()->updateOrCreate(
                ['codigo' => $demo['codigo']],
                $demo
            );
        }
    }
}
