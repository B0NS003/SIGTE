<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $ruta = database_path('seeders/data/servicios.json');
        $filas = json_decode(File::get($ruta), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($filas): void {
            foreach (array_chunk($filas, 100) as $grupo) {
                DB::table('servicios')->upsert(
                    array_map(fn (array $fila) => $fila + [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $grupo),
                    ['id'],
                    ['codigo', 'nombre']
                );
            }

            // Los IDs provienen de la exportación; sincronizar la secuencia PostgreSQL.
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SELECT setval(pg_get_serial_sequence('servicios', 'id'), COALESCE((SELECT MAX(id) FROM servicios), 1), (SELECT COUNT(*) > 0 FROM servicios))");
            }
        });
    }
}
