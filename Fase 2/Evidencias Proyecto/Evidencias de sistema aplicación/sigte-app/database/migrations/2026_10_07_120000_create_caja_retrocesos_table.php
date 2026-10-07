<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_retrocesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('etapa_desde', 32);
            $table->string('etapa_hacia', 32);
            $table->string('motivo', 180);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_retrocesos');
    }
};
