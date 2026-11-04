<?php

namespace Database\Seeders;

use App\Models\Ficha;
use App\Models\FichaInstrumento;
use App\Models\Instrumento;
use App\Models\TipoConjunto;
use Illuminate\Database\Seeder;

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

        /** @var array<int, int> $mapaTipos id JSON → id BD */
        $mapaTipos = [];
        foreach ($tiposJson as $tipo) {
            $registro = TipoConjunto::query()->updateOrCreate(
                ['codigo' => (string) $tipo['codigo']],
                [
                    'nombre_conjunto' => (string) $tipo['nombre_conjunto'],
                    'servicio_id' => $tipo['servicio_id'] ?? null,
                ]
            );
            $mapaTipos[(int) $tipo['id']] = (int) $registro->id;
        }

        $instrumentosNecesarios = collect($lineasJson)
            ->pluck('instrumento_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $porIdJson = collect($instrumentosJson)->keyBy(fn ($i) => (int) $i['id']);

        /** @var array<int, int> $mapaInstrumentos */
        $mapaInstrumentos = [];
        foreach ($instrumentosNecesarios as $idJson) {
            $dato = $porIdJson->get($idJson);
            if (! is_array($dato)) {
                continue;
            }
            $tipoId = isset($dato['tipo_conjunto_id'])
                ? ($mapaTipos[(int) $dato['tipo_conjunto_id']] ?? null)
                : null;

            $registro = Instrumento::query()->create([
                'nombre' => mb_substr((string) ($dato['nombre'] ?? 'Instrumento'), 0, 160),
                'tamano' => mb_substr((string) ($dato['tamano'] ?? ''), 0, 40),
                'codigo_fabricante' => mb_substr((string) ($dato['codigo_fabricante'] ?? ''), 0, 80),
                'tipo_conjunto_id' => $tipoId,
            ]);
            $mapaInstrumentos[$idJson] = (int) $registro->id;
        }

        /** @var array<int, int> $mapaFichas */
        $mapaFichas = [];
        foreach ($fichasJson as $ficha) {
            $tipoId = $mapaTipos[(int) ($ficha['tipo_conjunto_id'] ?? 0)] ?? null;
            $registro = Ficha::query()->updateOrCreate(
                ['codigo' => (string) $ficha['codigo']],
                [
                    'nombre' => mb_substr((string) $ficha['nombre'], 0, 160),
                    'tipo' => (($ficha['tipo'] ?? 'caja') === 'set') ? 'set' : 'caja',
                    'servicio_id' => $ficha['servicio_id'] ?? null,
                    'tipo_conjunto_id' => $tipoId,
                    'guia_visual' => (bool) ($ficha['guia_visual'] ?? false),
                ]
            );
            $registro->items()->delete();
            $mapaFichas[(int) $ficha['id']] = (int) $registro->id;
        }

        foreach ($lineasJson as $linea) {
            $fichaId = $mapaFichas[(int) $linea['ficha_id']] ?? null;
            $instrumentoId = $mapaInstrumentos[(int) $linea['instrumento_id']] ?? null;
            if ($fichaId === null || $instrumentoId === null) {
                continue;
            }
            $tipoId = isset($linea['tipo_conjunto_id'])
                ? ($mapaTipos[(int) $linea['tipo_conjunto_id']] ?? null)
                : null;

            FichaInstrumento::query()->create([
                'ficha_id' => $fichaId,
                'instrumento_id' => $instrumentoId,
                'tipo_conjunto_id' => $tipoId,
                'cantidad' => max(1, (int) ($linea['cantidad'] ?? 1)),
                'orden' => (int) ($linea['orden'] ?? 0),
            ]);
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
