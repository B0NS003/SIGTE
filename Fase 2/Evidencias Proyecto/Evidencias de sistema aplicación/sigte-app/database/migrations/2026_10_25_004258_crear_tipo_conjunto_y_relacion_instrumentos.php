
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_conjunto', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre_conjunto', 200);
            $table->foreignId('servicio_id')
                ->nullable()
                ->constrained('servicios')
                ->restrictOnDelete();
            $table->timestamps();
        });

        Schema::table('fichas', function (Blueprint $table) {
            $table->foreignId('tipo_conjunto_id')
                ->nullable()
                ->constrained('tipo_conjunto')
                ->restrictOnDelete();
        });

        Schema::table('ficha_instrumentos', function (Blueprint $table) {
            $table->foreignId('tipo_conjunto_id')
                ->nullable()
                ->constrained('tipo_conjunto')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ficha_instrumentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_conjunto_id');
        });

        Schema::table('fichas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_conjunto_id');
        });

        Schema::dropIfExists('tipo_conjunto');
    }
};
