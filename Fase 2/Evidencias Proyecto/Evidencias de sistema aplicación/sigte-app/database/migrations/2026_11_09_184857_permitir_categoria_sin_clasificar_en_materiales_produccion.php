
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE materiales_produccion
             DROP CONSTRAINT IF EXISTS materiales_produccion_categoria_check'
        );

        DB::statement("
            ALTER TABLE materiales_produccion
            ADD CONSTRAINT materiales_produccion_categoria_check
            CHECK (
                categoria IN (
                    'instrumental',
                    'ropa',
                    'insumos',
                    'sin_clasificar'
                )
            )
        ");
    }

    public function down(): void
    {
        // No se elimina la categoría automáticamente,
        // porque pueden existir materiales que la utilizan.
    }
};
