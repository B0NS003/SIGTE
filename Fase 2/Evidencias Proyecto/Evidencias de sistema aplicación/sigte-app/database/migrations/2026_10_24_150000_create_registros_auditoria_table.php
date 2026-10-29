<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 120);
            $table->string('tipo', 40);
            $table->string('objeto', 120)->nullable();
            $table->string('detalle', 255)->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['tipo', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_auditoria');
    }
};
