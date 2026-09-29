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
                'estado' => 'En autoclave 2',
                'urgente' => true,
                'responsable_id' => $operador?->id,
                'etapa_desde' => now()->subHours(4),
            ],
            [
                'codigo' => 'CAJA-118',
                'servicio' => 'Urgencia',
                'etapa' => Caja::ETAPA_LAVADO,
                'ubicacion' => 'Sala lavado',
                'estado' => 'En lavado (ciclo ~1h)',
                'urgente' => false,
                'responsable_id' => $enfermera?->id,
                'etapa_desde' => now()->subMinutes(55),
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
        ];

        foreach ($demos as $demo) {
            Caja::query()->updateOrCreate(
                ['codigo' => $demo['codigo']],
                $demo
            );
        }
    }
}
