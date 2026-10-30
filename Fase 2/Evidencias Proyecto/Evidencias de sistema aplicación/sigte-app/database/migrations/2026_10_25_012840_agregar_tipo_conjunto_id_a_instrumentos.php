<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrumentos', function (Blueprint $table) {
            $table->foreignId('tipo_conjunto_id')
                ->nullable()
                ->constrained('tipo_conjunto')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('instrumentos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_conjunto_id');
        });
    }
};
