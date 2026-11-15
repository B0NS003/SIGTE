<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ruta en disco para fotos de catálogo (uploads/), además del BYTEA legado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fichas', function (Blueprint $table) {
            $table->string('imagen_ruta', 255)->nullable()->after('imagen_formato');
        });

        Schema::table('instrumentos', function (Blueprint $table) {
            $table->string('imagen_ruta', 255)->nullable()->after('imagen_formato');
        });
    }

    public function down(): void
    {
        Schema::table('fichas', function (Blueprint $table) {
            $table->dropColumn('imagen_ruta');
        });

        Schema::table('instrumentos', function (Blueprint $table) {
            $table->dropColumn('imagen_ruta');
        });
    }
};
