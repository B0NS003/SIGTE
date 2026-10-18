<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('users')->orderBy('id')->pluck('id');
        $mapa = [];
        $siguiente = 1;
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id !== $siguiente) {
                $mapa[$id] = $siguiente;
            }
            $siguiente++;
        }

        if ($mapa !== []) {
            $referencias = [
                ['cajas', 'responsable_id'],
                ['consumos_servicio', 'user_id'],
                ['movimientos_insumo', 'user_id'],
                ['caja_anotaciones', 'user_id'],
                ['caja_retrocesos', 'user_id'],
                ['reposiciones', 'user_id'],
                ['sessions', 'user_id'],
            ];

            DB::statement('SET session_replication_role = replica');
            foreach ($mapa as $viejo => $nuevo) {
                DB::table('users')->where('id', $viejo)->update(['id' => -$viejo]);
            }
            foreach ($mapa as $viejo => $nuevo) {
                DB::table('users')->where('id', -$viejo)->update(['id' => $nuevo]);
                foreach ($referencias as [$tabla, $columna]) {
                    DB::table($tabla)->where($columna, $viejo)->update([$columna => $nuevo]);
                }
            }
            DB::statement('SET session_replication_role = DEFAULT');
            DB::table('sessions')->delete();
        }

        DB::statement("SELECT setval('users_id_seq', (SELECT COALESCE(MAX(id), 1) FROM users))");
    }

    public function down(): void
    {
        // Los id ya quedaron en uso en el resto de las tablas.
    }
};
