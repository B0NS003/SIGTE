<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elementos_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('codigo');
            $table->string('sala', 16);
            $table->string('nombre');
            $table->string('ubicacion')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('minimo')->default(0);
            $table->integer('en_proceso')->default(0);
            $table->timestamps();

            $table->unique(['sala', 'codigo']);
        });

        Schema::create('reposiciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elemento_inventario_id')->constrained('elementos_inventario')->cascadeOnDelete();
            $table->unsignedInteger('cantidad');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reposiciones');
        Schema::dropIfExists('elementos_inventario');
    }
};
