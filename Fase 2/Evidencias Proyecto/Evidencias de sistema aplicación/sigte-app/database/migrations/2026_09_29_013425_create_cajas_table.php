<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('servicio');
            $table->string('etapa');
            $table->string('ubicacion')->nullable();
            $table->string('estado')->nullable();
            $table->boolean('urgente')->default(false);
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('etapa_desde')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
