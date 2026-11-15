<?php

namespace Database\Seeders;

use App\Models\ElementoInventario;
use App\Models\Sala;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Inventario real desde JSON (rama recuperar-mi-inventario).
 * Solo salas lavado y armado. Lo que no esté en el JSON se elimina.
 */
class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        $inventarios = [
            'lavado' => 'inventario_lavado.json',
            'armado' => 'inventario_armado.json',
        ];

        $imagenesPorSala = [
            'lavado' => [
                'CEP-ART' => 'Cepillos para articulaciones y bisagras.jpg',
                'HIS-LUM' => 'Hisopos para limpieza de lúmenes.jpg',
                'DET-LAV' => 'Detergente para lavadora instrumental.jpg',
                'TIR-DET' => 'Tiras de control de concentración.jpg',
                'CON-TRA' => 'Contenedores para traslado de instrumental contaminado.jpg',
                'VIS-FAC' => 'Protectores faciales.jpg',
                'GUA-LAR' => 'Guantes largos resistentes a químicos.jpg',
                'BAN-INS' => 'Bandejas para instrumental.jpg',
                'REC-INS' => 'Recipientes para remojo.jpg',
                'PIS-ABS' => 'Paños absorbentes desechables.jpg',
                'ESC-INT' => 'Escobillas para limpieza de instrumental.jpg',
                'MASC-RES' => 'Mascarillas de protección respiratoria.jpg',
                'ETI-IDE' => 'Etiquetas para identificación.jpg',
            ],
            'armado' => [
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
            ],
        ];

        $tieneImagen = Schema::hasColumn('elementos_inventario', 'imagen');
        $codigosPorSala = [];

        foreach ($inventarios as $codigoSala => $archivo) {
            $ruta = database_path('seeders/data/'.$archivo);

            if (! is_file($ruta)) {
                throw new RuntimeException("No se encontró el archivo JSON: {$ruta}");
            }

            $datos = json_decode((string) file_get_contents($ruta), true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($datos)) {
                throw new RuntimeException("El archivo JSON {$archivo} no es válido.");
            }

            $sala = Sala::query()->where('codigo', $codigoSala)->firstOrFail();
            $codigosPorSala[(int) $sala->id] = [];

            foreach ($datos as $dato) {
                $codigo = (string) $dato['codigo'];
                $codigosPorSala[(int) $sala->id][] = $codigo;

                $valores = [
                    'nombre' => (string) $dato['nombre'],
                    'ubicacion' => $dato['ubicacion'] ?? null,
                    'stock' => (int) ($dato['stock'] ?? 0),
                    'minimo' => (int) ($dato['minimo'] ?? 0),
                    'en_proceso' => (int) ($dato['en_proceso'] ?? 0),
                ];

                if ($tieneImagen) {
                    $archivoImagen = $imagenesPorSala[$codigoSala][$codigo] ?? null;
                    $valores['imagen'] = $archivoImagen
                        ? 'images/inventario/'.$codigoSala.'/'.$archivoImagen
                        : null;
                }

                ElementoInventario::query()->updateOrCreate(
                    [
                        'sala_id' => $sala->id,
                        'codigo' => $codigo,
                    ],
                    $valores
                );
            }
        }

        // Solo quedan códigos del JSON en lavado/armado.
        foreach ($codigosPorSala as $salaId => $codigos) {
            ElementoInventario::query()
                ->where('sala_id', $salaId)
                ->whereNotIn('codigo', $codigos)
                ->delete();
        }

        // No hay JSON de material estéril: se quita el demo de esa sala.
        $salaEsterilId = Sala::query()->where('codigo', 'esteril')->value('id');
        if ($salaEsterilId !== null) {
            ElementoInventario::query()->where('sala_id', $salaEsterilId)->delete();
        }
    }
}
