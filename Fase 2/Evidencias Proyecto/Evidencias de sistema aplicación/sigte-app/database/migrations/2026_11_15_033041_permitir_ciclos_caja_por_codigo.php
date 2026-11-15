<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Opción B reingreso: un ciclo = una fila.
 * UNIQUE global de codigo → unique parcial solo mientras entregado_at IS NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropUnique('cajas_codigo_unique');
        });

        // Búsquedas por código (varios ciclos históricos).
        Schema::table('cajas', function (Blueprint $table) {
            $table->index('codigo', 'cajas_codigo_index');
        });

        DB::statement(
            'CREATE UNIQUE INDEX cajas_codigo_en_flujo_unique ON cajas (codigo) WHERE entregado_at IS NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS cajas_codigo_en_flujo_unique');

        Schema::table('cajas', function (Blueprint $table) {
            $table->dropIndex('cajas_codigo_index');
        });

        // Solo restaura UNIQUE si no hay códigos duplicados (varios ciclos).
        $duplicados = DB::table('cajas')
            ->select('codigo')
            ->groupBy('codigo')
            ->havingRaw('count(*) > 1')
            ->exists();

        if (! $duplicados) {
            Schema::table('cajas', function (Blueprint $table) {
                $table->unique('codigo', 'cajas_codigo_unique');
            });
        }
    }
};
