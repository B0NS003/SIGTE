<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->timestamp('proceso_desde')->nullable()->after('etapa_desde');
            $table->timestamp('proceso_hasta')->nullable()->after('proceso_desde');
        });
    }

    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropColumn(['proceso_desde', 'proceso_hasta']);
        });
    }
};
