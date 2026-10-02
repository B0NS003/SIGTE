<?php

namespace Database\Seeders;

use App\Models\ConsumoServicio;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConsumoServicioSeeder extends Seeder
{
    public function run(): void
    {
        $secretariaId = User::query()->where('email', 'secretaria@sigte.local')->value('id');

        $demos = [
            ['servicio' => 'Pabellon', 'periodo' => '2026-09-01', 'consumo' => 42, 'litros' => 520.00, 'observacion' => 'Cierre septiembre'],
            ['servicio' => 'Dental', 'periodo' => '2026-09-01', 'consumo' => 18, 'litros' => 95.50, 'observacion' => null],
            ['servicio' => 'Maternidad', 'periodo' => '2026-09-01', 'consumo' => 27, 'litros' => 310.00, 'observacion' => null],
            ['servicio' => 'Urgencia', 'periodo' => '2026-09-01', 'consumo' => 15, 'litros' => 140.00, 'observacion' => null],
            ['servicio' => 'Pabellon', 'periodo' => '2026-10-01', 'consumo' => 6, 'litros' => 72.00, 'observacion' => 'Avance de octubre'],
            ['servicio' => 'Dental', 'periodo' => '2026-10-01', 'consumo' => 3, 'litros' => 18.00, 'observacion' => null],
        ];

        foreach ($demos as $demo) {
            ConsumoServicio::query()->updateOrCreate(
                [
                    'servicio' => $demo['servicio'],
                    'periodo' => $demo['periodo'],
                ],
                [
                    'consumo' => $demo['consumo'],
                    'litros' => $demo['litros'],
                    'observacion' => $demo['observacion'],
                    'user_id' => $secretariaId,
                ]
            );
        }
    }
}
