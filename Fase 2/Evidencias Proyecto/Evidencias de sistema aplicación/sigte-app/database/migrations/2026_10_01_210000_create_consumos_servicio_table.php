<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumos_servicio', function (Blueprint $table) {
            $table->id();
            $table->string('servicio');
            $table->date('periodo');
            $table->unsignedInteger('consumo');
            $table->decimal('litros', 10, 2);
            $table->string('observacion')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['servicio', 'periodo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumos_servicio');
    }
};
