<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fichas', function (Blueprint $table) {
            $table->string('numero', 40)->nullable()->after('nombre');
            $table->string('codigo_referencia', 80)->nullable()->after('numero');
        });
    }

    public function down(): void
    {
        Schema::table('fichas', function (Blueprint $table) {
            $table->dropColumn(['numero', 'codigo_referencia']);
        });
    }
};
