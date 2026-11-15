<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo legacy vacío (reemplazado por fichas / instrumentos / tipo_conjunto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('catalogo_instrumentos');
        Schema::dropIfExists('catalogo_cajas');
    }

    public function down(): void
    {
        Schema::create('catalogo_cajas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('tipo');
            $table->string('servicio');
            $table->timestamps();
        });

        Schema::create('catalogo_instrumentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalogo_caja_id')->constrained('catalogo_cajas')->cascadeOnDelete();
            $table->string('nombre');
            $table->integer('cantidad');
            $table->timestamps();
        });
    }
};
