<?php

namespace Database\Seeders;

use App\Models\ElementoInventario;
use App\Models\Sala;
use Illuminate\Database\Seeder;
use RuntimeException;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        $inventarios = [
            'lavado' => 'inventario_lavado.json',
            'armado' => 'inventario_armado.json',
        ];

        $imagenesArmado = [
            'PAP-GRA' => 'Papel grado médico.jpg',
            'BOL-EST' => 'Bolsas para esterilización.jpg',
            'ROL-EST' => 'Rollos para esterilización.jpg',
            'IND-EXT' => 'Indicadores químicos externos.jpg',
            'IND-BIO' => 'Indicadores biológicos.jpg',
            'CINT-IND' => 'Cinta indicadora para esterilización.jpg',
            'SEL-EMP' => 'Sellos para bolsas de esterilización.jpg',
            'PAP-ABS' => 'Papel absorbente para preparación.jpg',
            'BAN-ORG' => 'Bandejas organizadoras.jpg',
            'SOP-INS' => 'Soportes para instrumental.jpg',
            'PRO-FIL' => 'Protectores para filos.jpg',
            'ETQ-EST' => 'Etiquetas para paquetes estériles.jpg',
            'MAR-ROT' => 'Marcadores resistentes al proceso.jpg',
            'LUZ-INS' => 'Lámpara de inspección.jpg',
            'CAR-TRA' => 'Carros para traslado de sets preparados.jpg',
        ];

        foreach ($inventarios as $codigoSala => $archivo) {
            $ruta = database_path("seeders/data/{$archivo}");

            if (! is_file($ruta)) {
                throw new RuntimeException("No se encontró el archivo JSON: {$ruta}");
            }

            $datos = json_decode(file_get_contents($ruta), true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($datos)) {
                throw new RuntimeException("El archivo JSON {$archivo} no es válido.");
            }

            $sala = Sala::query()
                ->where('codigo', $codigoSala)
                ->firstOrFail();

            foreach ($datos as $dato) {
                $elemento = ElementoInventario::query()->firstOrCreate(
                    [
                        'sala_id' => $sala->id,
                        'codigo' => $dato['codigo'],
                    ],
                    [
                        'nombre' => $dato['nombre'],
                        'ubicacion' => $dato['ubicacion'] ?? null,
                        'stock' => (int) ($dato['stock'] ?? 0),
                        'minimo' => (int) ($dato['minimo'] ?? 0),
                        'en_proceso' => (int) ($dato['en_proceso'] ?? 0),
                    ]
                );

                if ($codigoSala === 'armado' && isset($imagenesArmado[$dato['codigo']])) {
                    $elemento->update([
                        'imagen' => 'images/inventario/armado/' . $imagenesArmado[$dato['codigo']],
                    ]);
                }
            }
        }
    }
}
