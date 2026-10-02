<?php

namespace Database\Seeders;

use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\User;
use Illuminate\Database\Seeder;

class InsumoSeeder extends Seeder
{
    public function run(): void
    {
        $secretariaId = User::query()->where('email', 'secretaria@sigte.local')->value('id');

        $insumos = [
            ['codigo' => 'DET-ENZ', 'nombre' => 'Detergente enzimatico', 'ubicacion' => 'Bodega area sucia', 'stock' => 8, 'minimo' => 5],
            ['codigo' => 'IND-CLASE5', 'nombre' => 'Indicadores clase 5', 'ubicacion' => 'Estante armado', 'stock' => 12, 'minimo' => 20],
            ['codigo' => 'WRAP-SMS', 'nombre' => 'Envoltorio SMS 60x60', 'ubicacion' => 'Mesas de armado', 'stock' => 35, 'minimo' => 25],
            ['codigo' => 'ESP-SEC', 'nombre' => 'Esponjas / material secado', 'ubicacion' => 'Puesto lavado', 'stock' => 4, 'minimo' => 6],
        ];

        foreach ($insumos as $datos) {
            Insumo::query()->updateOrCreate(
                ['codigo' => $datos['codigo']],
                $datos
            );
        }

        $detergente = Insumo::query()->where('codigo', 'DET-ENZ')->first();
        if ($detergente === null) {
            return;
        }

        MovimientoInsumo::query()->firstOrCreate(
            [
                'insumo_id' => $detergente->id,
                'observacion' => 'Carga inicial de bodega',
            ],
            [
                'tipo' => MovimientoInsumo::ENTRADA,
                'cantidad' => 10,
                'user_id' => $secretariaId,
            ]
        );

        MovimientoInsumo::query()->firstOrCreate(
            [
                'insumo_id' => $detergente->id,
                'observacion' => 'Uso de septiembre en lavado',
            ],
            [
                'tipo' => MovimientoInsumo::SALIDA,
                'cantidad' => 2,
                'user_id' => $secretariaId,
            ]
        );
    }
}
