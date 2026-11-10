<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLAS = [
        'cajas' => 'responsable_id',
        'caja_anotaciones' => 'user_id',
        'caja_retrocesos' => 'user_id',
    ];

    public function up(): void
    {
        foreach (self::TABLAS as $tabla => $despues) {
            if (Schema::hasColumn($tabla, 'personal_id')) {
                continue;
            }
            Schema::table($tabla, function (Blueprint $table) use ($despues) {
                $table->foreignId('personal_id')->nullable()->after($despues)->constrained('personal')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::TABLAS) as $tabla) {
            if (! Schema::hasColumn($tabla, 'personal_id')) {
                continue;
            }
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropConstrainedForeignId('personal_id');
            });
        }
    }
};
