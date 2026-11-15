<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Endurece valores de turno en personal y cierres_turno.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE personal
            ADD CONSTRAINT personal_turno_check
            CHECK (turno IS NULL OR turno IN ('manana', 'tarde', 'noche'))
        ");

        DB::statement("
            ALTER TABLE cierres_turno
            ADD CONSTRAINT cierres_turno_clave_check
            CHECK (turno_clave IN ('manana', 'tarde', 'noche'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE personal DROP CONSTRAINT IF EXISTS personal_turno_check');
        DB::statement('ALTER TABLE cierres_turno DROP CONSTRAINT IF EXISTS cierres_turno_clave_check');
    }
};
