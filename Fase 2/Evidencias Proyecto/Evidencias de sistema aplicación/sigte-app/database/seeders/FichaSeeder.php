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
        $mapaFichaTipo = [];
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
            $mapaFichaTipo[(int) $ficha['id']] = $tipoId;
        }

        $ordenPorFicha = [];
        foreach ($lineasJson as $linea) {
            $fichaJsonId = (int) $linea['ficha_id'];
            $fichaId = $mapaFichas[$fichaJsonId] ?? null;
            $instrumentoId = $mapaInstrumentos[(int) $linea['instrumento_id']] ?? null;
            $fichaTipoId = $mapaFichaTipo[$fichaJsonId] ?? null;
            if ($fichaId === null || $instrumentoId === null || $fichaTipoId === null) {
                continue;
            }

            // D2: no enlazar instrumento de otro tipo (UNIQUE nombre+tamaño+código puede colisionar).
            $instTipoId = Instrumento::query()->where('id', $instrumentoId)->value('tipo_conjunto_id');
            if ((int) $instTipoId !== (int) $fichaTipoId) {
                continue;
            }

            $ordenPorFicha[$fichaId] = ($ordenPorFicha[$fichaId] ?? 0) + 1;

            FichaInstrumento::query()->create([
                'ficha_id' => $fichaId,
                'instrumento_id' => $instrumentoId,
                'tipo_conjunto_id' => $fichaTipoId,
                'cantidad' => max(1, (int) ($linea['cantidad'] ?? 1)),
                'orden' => $ordenPorFicha[$fichaId],
            ]);
        }

        // Fichas que solo tenían instrumentos de otro tipo (colisión UNIQUE) quedan sin ítems.
        Ficha::query()->whereDoesntHave('items')->delete();
    }

    private function limpiarCatalogo(): void
    {
        // No tocar cajas ni ficha_id: el flujo se limpia aparte y las recepciones reales enlazan ficha.
        // Sin pivot tipo_conjunto_instrumentos: el vínculo es instrumentos.tipo_conjunto_id.
        foreach (['ficha_instrumentos', 'instrumentos', 'fichas', 'tipo_conjunto'] as $tabla) {
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
