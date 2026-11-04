<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstrumentoSeeder extends Seeder
{
    public function run(): void
    {
        $ruta = database_path('seeders/data/instrumentos.json');
        $filas = json_decode(File::get($ruta), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($filas): void {
            foreach (array_chunk($filas, 100) as $grupo) {
                DB::table('instrumentos')->upsert(
                    array_map(fn (array $fila) => $fila + [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $grupo),
                    ['id'],
                    ['nombre', 'tamano', 'codigo_fabricante', 'tipo_conjunto_id']
                );
            }

            // Los IDs provienen de la exportación; sincronizar la secuencia PostgreSQL.
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SELECT setval(pg_get_serial_sequence('instrumentos', 'id'), COALESCE((SELECT MAX(id) FROM instrumentos), 1), (SELECT COUNT(*) > 0 FROM instrumentos))");
            }
        });
    }
}
