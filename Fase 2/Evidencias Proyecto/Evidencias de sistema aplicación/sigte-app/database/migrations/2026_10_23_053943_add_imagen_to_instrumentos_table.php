
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE instrumentos ADD COLUMN imagen BYTEA NULL');

        Schema::table('instrumentos', function (Blueprint $table) {
            $table->string('imagen_formato', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('instrumentos', function (Blueprint $table) {
            $table->dropColumn('imagen_formato');
        });

        DB::statement('ALTER TABLE instrumentos DROP COLUMN imagen');
    }
};
