<?php

namespace Database\Seeders;

use App\Models\Ficha;
use App\Models\FichaInstrumento;
use App\Models\Instrumento;
use App\Models\TipoConjunto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Catálogo desde JSON de fichas_catalogo / instrumentos / tipo_conjunto. */
class FichaSeeder extends Seeder
{
    public function run(): void
    {
        $tiposJson = $this->leer('tipo_conjunto.json');
        $fichasJson = $this->leer('fichas_catalogo.json');
        $instrumentosJson = $this->leer('instrumentos.json');
        $lineasJson = $this->leer('ficha_instrumentos.json');

        if ($tiposJson === null || $fichasJson === null || $instrumentosJson === null || $lineasJson === null) {
            return;
        }

        $this->limpiarCatalogo();

        $mapaTipos = [];
        foreach ($tiposJson as $tipo) {
            $registro = TipoConjunto::query()->create([
                'codigo' => (string) $tipo['codigo'],
                'nombre_conjunto' => (string) $tipo['nombre_conjunto'],
                'servicio_id' => $tipo['servicio_id'] ?? null,
            ]);
            $mapaTipos[(int) $tipo['id']] = (int) $registro->id;
        }

        $porIdJson = collect($instrumentosJson)->keyBy(fn ($i) => (int) $i['id']);
        $mapaInstrumentos = [];

        foreach (collect($lineasJson)->pluck('instrumento_id')->map(fn ($id) => (int) $id)->unique() as $idJson) {
            $dato = $porIdJson->get($idJson);
            if (! is_array($dato)) {
                continue;
            }

            $nombre = mb_substr(trim((string) ($dato['nombre'] ?? 'Instrumento')), 0, 160);
            $tamano = mb_substr(trim((string) ($dato['tamano'] ?? '')), 0, 40);
            $codigoFab = mb_substr(trim((string) ($dato['codigo_fabricante'] ?? '')), 0, 80);
            $tipoId = isset($dato['tipo_conjunto_id'])
                ? ($mapaTipos[(int) $dato['tipo_conjunto_id']] ?? null)
                : null;

            $clave = mb_strtolower($nombre).'|'.$tamano.'|'.$codigoFab;
            if (isset($mapaInstrumentos['__por_clave'][$clave])) {
                $mapaInstrumentos[$idJson] = $mapaInstrumentos['__por_clave'][$clave];
                continue;
            }

            $registro = Instrumento::query()->firstOrCreate(
                [
                    'nombre' => $nombre,
                    'tamano' => $tamano,
                    'codigo_fabricante' => $codigoFab,
                ],
                ['tipo_conjunto_id' => $tipoId]
            );

            $mapaInstrumentos['__por_clave'][$clave] = (int) $registro->id;
            $mapaInstrumentos[$idJson] = (int) $registro->id;
        }
        unset($mapaInstrumentos['__por_clave']);

        $mapaFichas = [];
        foreach ($fichasJson as $ficha) {
            $tipoId = $mapaTipos[(int) ($ficha['tipo_conjunto_id'] ?? 0)] ?? null;
            $registro = Ficha::query()->create([
                'codigo' => (string) $ficha['codigo'],
                'nombre' => mb_substr((string) $ficha['nombre'], 0, 160),
                'tipo' => (($ficha['tipo'] ?? 'caja') === 'set') ? 'set' : 'caja',
                'servicio_id' => $ficha['servicio_id'] ?? null,
                'tipo_conjunto_id' => $tipoId,
                'guia_visual' => (bool) ($ficha['guia_visual'] ?? false),
            ]);
            $mapaFichas[(int) $ficha['id']] = (int) $registro->id;
        }

        foreach ($lineasJson as $linea) {
            $fichaId = $mapaFichas[(int) $linea['ficha_id']] ?? null;
            $instrumentoId = $mapaInstrumentos[(int) $linea['instrumento_id']] ?? null;
            if ($fichaId === null || $instrumentoId === null) {
                continue;
            }

            FichaInstrumento::query()->create([
                'ficha_id' => $fichaId,
                'instrumento_id' => $instrumentoId,
                'tipo_conjunto_id' => isset($linea['tipo_conjunto_id'])
                    ? ($mapaTipos[(int) $linea['tipo_conjunto_id']] ?? null)
                    : null,
                'cantidad' => max(1, (int) ($linea['cantidad'] ?? 1)),
                'orden' => (int) ($linea['orden'] ?? 0),
            ]);
        }
    }

    private function limpiarCatalogo(): void
    {
        if (Schema::hasTable('cajas') && Schema::hasColumn('cajas', 'ficha_id')) {
            DB::table('cajas')->update(['ficha_id' => null]);
        }
        foreach (['ficha_instrumentos', 'tipo_conjunto_instrumentos', 'catalogo_instrumentos', 'catalogo_cajas', 'instrumentos', 'fichas', 'tipo_conjunto'] as $tabla) {
            if (Schema::hasTable($tabla)) {
                DB::table($tabla)->delete();
            }
        }
    }

    /** @return list<array<string, mixed>>|null */
    private function leer(string $archivo): ?array
    {
        $ruta = database_path('seeders/data/'.$archivo);
        if (! is_file($ruta)) {
            return null;
        }
        $datos = json_decode((string) file_get_contents($ruta), true);

        return is_array($datos) ? $datos : null;
    }
}
