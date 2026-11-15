<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Optimización A+B+E: índices de consulta, CHECK de integridad y vista de producción mensual.
 * Sin triggers (quedan para después).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->index('etapa', 'cajas_etapa_index');
            $table->index('servicio_id', 'cajas_servicio_id_index');
            $table->index('ficha_id', 'cajas_ficha_id_index');
            $table->index('entregado_at', 'cajas_entregado_at_index');
        });

        Schema::table('fichas', function (Blueprint $table) {
            $table->index('servicio_id', 'fichas_servicio_id_index');
            $table->index('tipo_conjunto_id', 'fichas_tipo_conjunto_id_index');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->index('elemento_inventario_id', 'movimientos_inventario_elemento_id_index');
            $table->index('created_at', 'movimientos_inventario_created_at_index');
        });

        Schema::table('registros_produccion', function (Blueprint $table) {
            $table->index(['servicio_id', 'fecha'], 'registros_produccion_servicio_fecha_index');
            $table->index('material_id', 'registros_produccion_material_id_index');
        });

        DB::statement("
            ALTER TABLE cajas
            ADD CONSTRAINT cajas_etapa_check
            CHECK (etapa IN (
                'recepcion',
                'lavado',
                'preparacion',
                'esterilizacion',
                'almacen',
                'entrega'
            ))
        ");

        DB::statement('
            ALTER TABLE elementos_inventario
            ADD CONSTRAINT elementos_stock_check
            CHECK (stock >= 0 AND minimo >= 0 AND en_proceso >= 0)
        ');

        DB::statement("
            ALTER TABLE movimientos_inventario
            ADD CONSTRAINT movimientos_tipo_check
            CHECK (tipo IN (
                'entrada',
                'salida',
                'reposicion',
                'consumo',
                'uso',
                'devolucion'
            ))
        ");

        DB::statement('
            CREATE OR REPLACE VIEW v_produccion_mensual_servicio AS
            SELECT
                date_trunc(\'month\', fecha)::date AS periodo,
                servicio_id,
                sum(cantidad)::bigint AS unidades,
                round(sum(cantidad * factor_litros), 2) AS litros,
                count(*)::bigint AS registros
            FROM registros_produccion
            GROUP BY 1, 2
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_produccion_mensual_servicio');

        DB::statement('ALTER TABLE movimientos_inventario DROP CONSTRAINT IF EXISTS movimientos_tipo_check');
        DB::statement('ALTER TABLE elementos_inventario DROP CONSTRAINT IF EXISTS elementos_stock_check');
        DB::statement('ALTER TABLE cajas DROP CONSTRAINT IF EXISTS cajas_etapa_check');

        Schema::table('registros_produccion', function (Blueprint $table) {
            $table->dropIndex('registros_produccion_material_id_index');
            $table->dropIndex('registros_produccion_servicio_fecha_index');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropIndex('movimientos_inventario_created_at_index');
            $table->dropIndex('movimientos_inventario_elemento_id_index');
        });

        Schema::table('fichas', function (Blueprint $table) {
            $table->dropIndex('fichas_tipo_conjunto_id_index');
            $table->dropIndex('fichas_servicio_id_index');
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->dropIndex('cajas_entregado_at_index');
            $table->dropIndex('cajas_ficha_id_index');
            $table->dropIndex('cajas_servicio_id_index');
            $table->dropIndex('cajas_etapa_index');
        });
    }
};
