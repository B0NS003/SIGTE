<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class FichaSeeder extends Seeder
{
    public function run(): void
    {
        $ruta = database_path('seeders/data/fichas_catalogo.json');
        $fichas = json_decode(File::get($ruta), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($fichas): void {
            foreach ($fichas as $ficha) {
                $id = (int) $ficha['id'];
                $codigo = (string) $ficha['codigo'];

                // Las relaciones exportadas apuntan al ID original de la ficha.
                $otroCodigo = DB::table('fichas')->where('id', $id)->where('codigo', '<>', $codigo)->exists();
                $otroId = DB::table('fichas')->where('codigo', $codigo)->where('id', '<>', $id)->exists();
                if ($otroCodigo || $otroId) {
                    throw new RuntimeException("Conflicto de ID o código para la ficha {$codigo} (ID {$id}). No se modificaron datos.");
                }

                // No sobrescribir imágenes existentes ni relaciones de instrumental.
                DB::table('fichas')->upsert([[
                    'id' => $id,
                    'codigo' => $codigo,
                    'nombre' => $ficha['nombre'],
                    'tipo' => $ficha['tipo'],
                    'servicio_id' => (int) $ficha['servicio_id'],
                    'tipo_conjunto_id' => (int) $ficha['tipo_conjunto_id'],
                    'guia_visual' => (bool) $ficha['guia_visual'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]], ['id'], [
                    'codigo', 'nombre', 'tipo', 'servicio_id', 'tipo_conjunto_id', 'guia_visual', 'updated_at',
                ]);
            }

            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SELECT setval(pg_get_serial_sequence('fichas', 'id'), COALESCE((SELECT MAX(id) FROM fichas), 1), (SELECT COUNT(*) > 0 FROM fichas))");
            }
        });
    }
}
