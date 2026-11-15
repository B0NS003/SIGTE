<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Paquete C (P3): al INSERT en movimientos_inventario, la BD actualiza stock / en_proceso.
 * No reescribe saldos históricos; solo aplica hacia adelante.
 * La app deja de sumar/restar stock en PHP para no duplicar.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION trg_movimientos_inventario_stock()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    delta_stock integer;
    delta_proceso integer := 0;
    actualizados integer;
BEGIN
    IF NEW.tipo IN ('entrada', 'reposicion', 'devolucion') THEN
        delta_stock := NEW.cantidad;
    ELSIF NEW.tipo IN ('salida', 'consumo', 'uso') THEN
        delta_stock := -NEW.cantidad;
    ELSE
        RAISE EXCEPTION 'Tipo de movimiento no soportado: %', NEW.tipo
            USING ERRCODE = 'check_violation';
    END IF;

    IF NEW.tipo = 'uso' THEN
        delta_proceso := NEW.cantidad;
    ELSIF NEW.tipo = 'devolucion' THEN
        delta_proceso := -NEW.cantidad;
    END IF;

    UPDATE elementos_inventario
    SET
        stock = stock + delta_stock,
        en_proceso = en_proceso + delta_proceso,
        updated_at = NOW()
    WHERE id = NEW.elemento_inventario_id
      AND (delta_stock >= 0 OR stock >= NEW.cantidad)
      AND (delta_proceso >= 0 OR en_proceso >= NEW.cantidad);

    GET DIAGNOSTICS actualizados = ROW_COUNT;
    IF actualizados = 0 THEN
        RAISE EXCEPTION 'Stock o unidades en proceso insuficientes para el movimiento'
            USING ERRCODE = 'check_violation';
    END IF;

    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS movimientos_inventario_stock_ai ON movimientos_inventario;

CREATE TRIGGER movimientos_inventario_stock_ai
AFTER INSERT ON movimientos_inventario
FOR EACH ROW
EXECUTE PROCEDURE trg_movimientos_inventario_stock();
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS movimientos_inventario_stock_ai ON movimientos_inventario;
DROP FUNCTION IF EXISTS trg_movimientos_inventario_stock();
SQL);
    }
};
