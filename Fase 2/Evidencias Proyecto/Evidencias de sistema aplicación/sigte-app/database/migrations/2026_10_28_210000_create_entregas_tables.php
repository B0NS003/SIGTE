<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 32)->unique();
            $table->string('servicio', 80);
            $table->string('recibe', 150);
            $table->string('recibe_rol', 80)->nullable();
            $table->string('material', 150)->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('entregado_at');
            $table->timestamps();

            $table->index(['servicio', 'entregado_at']);
            $table->index('entregado_at');
        });

        Schema::create('entrega_cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrega_id')->constrained('entregas')->cascadeOnDelete();
            $table->foreignId('caja_id')->nullable()->constrained('cajas')->nullOnDelete();
            $table->string('codigo', 40);
            $table->string('nombre', 120)->nullable();
            $table->timestamps();

            $table->index('codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrega_cajas');
        Schema::dropIfExists('entregas');
    }
};
