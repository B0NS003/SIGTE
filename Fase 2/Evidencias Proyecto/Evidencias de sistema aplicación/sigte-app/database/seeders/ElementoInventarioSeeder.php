<?php

namespace Database\Seeders;

use App\Models\ElementoInventario;
use App\Models\Sala;
use Illuminate\Database\Seeder;

class ElementoInventarioSeeder extends Seeder
{
    public function run(): void
    {
        $elementos = [
            ['sala' => 'lavado', 'codigo' => 'DET-ENZ', 'nombre' => 'Detergente enzimatico', 'ubicacion' => 'Area sucia / remojo', 'stock' => 8, 'minimo' => 5, 'en_proceso' => 0],
            ['sala' => 'lavado', 'codigo' => 'EPP-KIT', 'nombre' => 'Kit EPP (pechera/gorro/guantes)', 'ubicacion' => 'Bodega area sucia', 'stock' => 22, 'minimo' => 15, 'en_proceso' => 0],
            ['sala' => 'lavado', 'codigo' => 'ESP-SEC', 'nombre' => 'Esponjas / material secado', 'ubicacion' => 'Puesto lavado', 'stock' => 4, 'minimo' => 6, 'en_proceso' => 0],
            ['sala' => 'armado', 'codigo' => 'WRAP-SMS', 'nombre' => 'Envoltorio SMS 60x60', 'ubicacion' => 'Mesas de armado', 'stock' => 35, 'minimo' => 25, 'en_proceso' => 0],
            ['sala' => 'armado', 'codigo' => 'IND-CLASE5', 'nombre' => 'Indicadores clase 5', 'ubicacion' => 'Estante armado', 'stock' => 12, 'minimo' => 20, 'en_proceso' => 0],
            ['sala' => 'armado', 'codigo' => 'CNT-EST-01', 'nombre' => 'Contenedor rigido 1/1', 'ubicacion' => 'Area preparacion', 'stock' => 6, 'minimo' => 4, 'en_proceso' => 2],
            ['sala' => 'esteril', 'codigo' => 'SET-LAP-01', 'nombre' => 'Set laparoscopia basico', 'ubicacion' => 'Almacen esteril A1', 'stock' => 4, 'minimo' => 3, 'en_proceso' => 2],
            ['sala' => 'esteril', 'codigo' => 'SET-CES-02', 'nombre' => 'Set cesarea', 'ubicacion' => 'Almacen esteril A2', 'stock' => 2, 'minimo' => 3, 'en_proceso' => 1],
            ['sala' => 'esteril', 'codigo' => 'SET-UCI-03', 'nombre' => 'Set via aerea UCI', 'ubicacion' => 'Almacen esteril A3', 'stock' => 1, 'minimo' => 2, 'en_proceso' => 1],
            ['sala' => 'esteril', 'codigo' => 'CAJ-CUR-10', 'nombre' => 'Caja curacion general', 'ubicacion' => 'Almacen esteril B1', 'stock' => 8, 'minimo' => 5, 'en_proceso' => 3],
        ];

        foreach ($elementos as $datos) {
            $salaId = Sala::query()->where('codigo', $datos['sala'])->value('id');
            unset($datos['sala']);
            $datos['sala_id'] = $salaId;
            ElementoInventario::query()->firstOrCreate(
                ['sala_id' => $salaId, 'codigo' => $datos['codigo']],
                $datos
            );
        }
    }
}
