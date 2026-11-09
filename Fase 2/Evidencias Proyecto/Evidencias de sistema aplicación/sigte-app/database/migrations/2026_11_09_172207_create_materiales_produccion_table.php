
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materiales_produccion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->enum('categoria', [
                'instrumental',
                'ropa',
                'insumos'
            ]);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiales_produccion');
    }
};
