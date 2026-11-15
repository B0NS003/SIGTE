<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El CHECK inicial solo cubría tipos de Secretaría.
 * El inventario de operadora usa consumo / uso / devolucion.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE movimientos_inventario DROP CONSTRAINT IF EXISTS movimientos_tipo_check');

        DB::statement("
            ALTER TABLE movimientos_inventario
            ADD CONSTRAINT movimientos_tipo_check
            CHECK (tipo IN (
                'entrada',
                'salida',
                'reposicion',
                'consumo',
                'uso',
                'devolucion'
            ))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE movimientos_inventario DROP CONSTRAINT IF EXISTS movimientos_tipo_check');

        DB::statement("
            ALTER TABLE movimientos_inventario
            ADD CONSTRAINT movimientos_tipo_check
            CHECK (tipo IN ('entrada', 'salida', 'reposicion'))
        ");
    }
};
