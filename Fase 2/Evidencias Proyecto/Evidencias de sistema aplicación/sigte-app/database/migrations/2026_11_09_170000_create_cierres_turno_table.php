<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cierres_turno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('relevo_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('relevo_nombre', 160);
            $table->string('turno_clave', 20);
            $table->string('turno_etiqueta', 80);
            $table->date('turno_fecha');
            $table->timestamp('cerrado_at');
            $table->text('observacion')->nullable();
            $table->boolean('check_alertas')->default(false);
            $table->boolean('check_criticas')->default(false);
            $table->boolean('check_stock')->default(false);
            $table->json('snapshot_alertas')->nullable();
            $table->json('snapshot_pendientes')->nullable();
            $table->json('snapshot_resumen')->nullable();
            $table->timestamps();

            $table->unique(['turno_fecha', 'turno_clave']);
            $table->index(['user_id', 'cerrado_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierres_turno');
    }
};
