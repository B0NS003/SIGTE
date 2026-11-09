
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_produccion', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');

            $table->foreignId('servicio_id')
                ->constrained('servicios');

            $table->foreignId('material_id')
                ->constrained('materiales_produccion');

            $table->unsignedInteger('cantidad');

            $table->decimal('factor_litros', 10, 2);

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users');

            $table->string('observacion', 255)->nullable();

            $table->timestamps();

            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_produccion');
    }
};
