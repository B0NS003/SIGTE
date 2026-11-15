<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paquete 2: índices para turnos (personal) y reportes por fecha (cajas / retrocesos).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal', function (Blueprint $table) {
            $table->index(['activo', 'turno'], 'personal_activo_turno_index');
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->index('created_at', 'cajas_created_at_index');
        });

        Schema::table('caja_retrocesos', function (Blueprint $table) {
            $table->index('created_at', 'caja_retrocesos_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('caja_retrocesos', function (Blueprint $table) {
            $table->dropIndex('caja_retrocesos_created_at_index');
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->dropIndex('cajas_created_at_index');
        });

        Schema::table('personal', function (Blueprint $table) {
            $table->dropIndex('personal_activo_turno_index');
        });
    }
};
