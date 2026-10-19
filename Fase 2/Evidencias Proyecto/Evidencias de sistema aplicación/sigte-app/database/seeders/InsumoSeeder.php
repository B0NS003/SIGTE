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

        $detergente = Insumo::query()->where('codigo', 'DET-ENZ')->first();
        if ($detergente === null) {
            return;
        }

        MovimientoInsumo::query()->firstOrCreate(
            [
                'elemento_inventario_id' => $detergente->id,
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
                'elemento_inventario_id' => $detergente->id,
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
