<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fichas', function (Blueprint $table) {
            $table->dropColumn([
                'area',
                'numero',
                'codigo_referencia',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('fichas', function (Blueprint $table) {
            $table->string('area')->nullable();
            $table->string('numero')->nullable();
            $table->string('codigo_referencia')->nullable();
        });
    }
};