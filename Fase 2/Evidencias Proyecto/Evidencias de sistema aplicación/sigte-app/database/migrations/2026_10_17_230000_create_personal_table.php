<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 160);
            $table->string('email', 255)->unique();
            $table->unsignedTinyInteger('grupo')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->foreignId('personal_id')->nullable()->after('responsable_id')->constrained('personal')->nullOnDelete();
        });

        Schema::table('caja_anotaciones', function (Blueprint $table) {
            $table->foreignId('personal_id')->nullable()->after('user_id')->constrained('personal')->nullOnDelete();
        });

        Schema::table('caja_retrocesos', function (Blueprint $table) {
            $table->foreignId('personal_id')->nullable()->after('user_id')->constrained('personal')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('caja_retrocesos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('personal_id');
        });
        Schema::table('caja_anotaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('personal_id');
        });
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('personal_id');
        });
        Schema::dropIfExists('personal');
    }
};
