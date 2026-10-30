<?php

namespace Database\Seeders;

use App\Models\Ficha;
use App\Models\FichaInstrumento;
use App\Models\Instrumento;
use App\Models\Servicio;
use App\Models\TipoConjunto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FichaSeeder extends Seeder
{
    public function run(): void
    {
        $ruta = database_path('seeders/data/fichas.json');
        if (! is_file($ruta)) {
            return;
        }

        $fichas = json_decode((string) file_get_contents($ruta), true);
        if (! is_array($fichas)) {
            return;
        }

        foreach ($fichas as $ficha) {
            $nombre = $this->nombre($ficha['nombre'] ?? '');
            if ($nombre === '') {
                continue;
            }

            $tipo = ($ficha['tipo'] ?? 'caja') === 'set' ? 'set' : 'caja';
            $servicioId = $this->servicioIdParaArea($this->area((string) ($ficha['hoja'] ?? '')));
            $tipoConjuntoId = $this->tipoConjuntoId($servicioId, $tipo);

            $registro = Ficha::query()->updateOrCreate(
                ['codigo' => $ficha['codigo']],
                [
                    'nombre' => $nombre,
                    'tipo' => $tipo,
                    'servicio_id' => $servicioId,
                    'tipo_conjunto_id' => $tipoConjuntoId,
                    'guia_visual' => false,
                ]
            );

            $registro->items()->delete();
            $orden = 0;
            foreach ($ficha['items'] ?? [] as $item) {
                $instrumentoNombre = trim((string) ($item['nombre'] ?? ''));
                if ($instrumentoNombre === '') {
                    continue;
                }
                $instrumento = Instrumento::query()->firstOrCreate(
                    [
                        'nombre' => mb_substr($instrumentoNombre, 0, 160),
                        'tamano' => mb_substr(trim((string) ($item['tamano'] ?? '')), 0, 40),
                        'codigo_fabricante' => mb_substr(trim((string) ($item['codigo'] ?? '')), 0, 80),
                    ]
                );
                FichaInstrumento::query()->updateOrCreate(
                    [
                        'ficha_id' => $registro->id,
                        'instrumento_id' => $instrumento->id,
                    ],
                    [
                        'cantidad' => max(1, (int) ($item['cantidad'] ?? 1)),
                        'orden' => $orden,
                    ]
                );
                $orden++;
            }
        }
    }

    private function area(string $hoja): string
    {
        $clave = mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $hoja) ?? ''));
        if ($clave === '' || preg_match('/^HOJA\d+$/u', $clave) === 1) {
            return 'Cirugía general';
        }

        return self::AREAS[$clave] ?? 'Cirugía general';
    }

    private function servicioIdParaArea(string $area): int
    {
        $nombre = match (true) {
            str_contains($area, 'Dental') => 'Dental',
            str_contains($area, 'Ginecología') || str_contains($area, 'obstetricia') => 'Maternidad',
            str_contains($area, 'Urgencia') => 'Urgencia',
            default => 'Pabellon',
        };

        return (int) (Servicio::query()->where('nombre', $nombre)->value('id')
            ?? Servicio::query()->where('codigo', 'general')->value('id')
            ?? Servicio::query()->value('id'));
    }

    private function tipoConjuntoId(int $servicioId, string $tipo): int
    {
        $codigo = Str::slug($tipo.'-'.$servicioId, '_');
        $nombre = $tipo === 'set' ? 'Set quirúrgico' : 'Caja quirúrgica';

        return (int) TipoConjunto::query()->firstOrCreate(
            ['codigo' => $codigo],
            [
                'nombre_conjunto' => $nombre,
                'servicio_id' => $servicioId,
            ]
        )->id;
    }

    /** @var array<string, string> */
    private const AREAS = [
        'APENDICE' => 'Cirugía general',
        'FIMOSIS' => 'Cirugía general',
        'CVC' => 'Cirugía general',
        'CAJA NUEVA HERNIA' => 'Cirugía general',
        'CAJA HERNIA INFANTIL Y ADULTO' => 'Cirugía general',
        'REVISION' => 'Cirugía general',
        'FINOQUETO' => 'Cirugía general',
        'DR. VILLARROEL' => 'Cirugía general',
        'CAJA DR. MORALES' => 'Cirugía general',
        'GASTRO' => 'Digestiva',
        'PROCTOLOGIA' => 'Digestiva',
        'COLON' => 'Digestiva',
        'COLE - LAP' => 'Digestiva',
        'COLE-LAP ANTIGUAS' => 'Digestiva',
        'BASE LAPAROSCOPICA' => 'Digestiva',
        'VESICULA' => 'Digestiva',
        'COLEDOCO' => 'Digestiva',
        'BARIATRICA' => 'Bariátrica',
        'ACCESORIOS BARIATRICA' => 'Bariátrica',
        'NIÑO' => 'Niño',
        'RN CATETER UMBILICAL' => 'Niño',
        'HISTERECTOMIAS LAP' => 'Ginecología y obstetricia',
        'BASES GINECOLOGICAS' => 'Ginecología y obstetricia',
        'FIBRA Y OPTICAS GINE' => 'Ginecología y obstetricia',
        'HISTEROSCOPIA' => 'Ginecología y obstetricia',
        'RASPADO' => 'Ginecología y obstetricia',
        'PROLAPSO O CERCLAJE' => 'Ginecología y obstetricia',
        'HISTERECTOMIA' => 'Ginecología y obstetricia',
        'CESAREA' => 'Ginecología y obstetricia',
        'CAJA CIRUGÍA VASCULAR' => 'Cirugía vascular',
        'CIRUGIA VASCULAR' => 'Cirugía vascular',
        'SET CINCELES' => 'Cirugía vascular',
        'UROLOGIA' => 'Urología',
        'RTU' => 'Urología',
        'URETERORRENOSCOPIO' => 'Urología',
        'URETERO SEMIRIGIDO' => 'Urología',
        'TOT' => 'Urología',
        'SHAVER STRYKER + FIBRA' => 'Otorrino',
        'ADENOAMIGDALAS' => 'Otorrino',
        'OTORRINO' => 'Otorrino',
        'RINOPLASTIA' => 'Otorrino',
        'TRAUMATOLOGIA' => 'Traumatología',
        'OPTICAS TRAUMA' => 'Traumatología',
        'TMT MENOR1,2,3' => 'Traumatología',
        'ARTROSCOPIA' => 'Traumatología',
        'STRIKER, OPTICA Y TROCAR' => 'Traumatología',
        'GRANDES FRAGMENTOS' => 'Traumatología',
        'DHS' => 'Traumatología',
        'P. FRAGM ANTIGUA' => 'Traumatología',
        'P. FRAGMENTOS' => 'Traumatología',
        'CAJA VITUS' => 'Traumatología',
        'CLAVO AFFIXUS' => 'Traumatología',
        'CAJA ZIPTIGHT' => 'Traumatología',
        'SET DE CADERA I' => 'Traumatología',
        'SET DE CADERA II' => 'Traumatología',
        'SET DE CADERA ANTHOLOGY' => 'Traumatología',
        'DR.CONTRERAS' => 'Traumatología',
        'PLASTICA' => 'Cirugía plástica',
        'CAJA CIRUGÍA PLASTICA NUEVA' => 'Cirugía plástica',
        'DERMATOMO HUMECA' => 'Cirugía plástica',
        'CAJA CHALAZIÓN OFT.' => 'Oftalmología',
        'OFTAL. PTERIGION 01 Y CAJA SOS' => 'Oftalmología',
        'OFTALMOLOGIA 2-6 CATARATAS' => 'Oftalmología',
        'ESTRABISMO' => 'Oftalmología',
        'PERIODONCIA' => 'Dental y maxilofacial',
        'CAJAS ROJAS DE IMPLANTES' => 'Dental y maxilofacial',
        'CIRUGIA DENTAL' => 'Dental y maxilofacial',
        'CIRUGIA DENTOALVEOLAR' => 'Dental y maxilofacial',
        'ORTOGNATICA - TRAUMA' => 'Dental y maxilofacial',
        'CAJA CIRUGÍA ORTOGNÁTICA' => 'Dental y maxilofacial',
        'BIOMATERIALES' => 'Dental y maxilofacial',
    ];

    private function nombre(string $nombre): string
    {
        $nombre = trim(preg_replace('/\s+/u', ' ', $nombre) ?? '');
        $nombre = preg_replace('/^\*?\s*nombre\s*[:\-–—]+\s*caja\s*:\s*/iu', '', $nombre) ?? $nombre;
        $nombre = preg_replace('/^\*?\s*nombre\s+caja\s*:\s*/iu', '', $nombre) ?? $nombre;
        $nombre = preg_replace('/^\*?\s*nombre\s*[:\-–—]+\s*/iu', '', $nombre) ?? $nombre;
        $nombre = preg_replace('/^\*?\s*nombre\s+/iu', '', $nombre) ?? $nombre;

        return mb_substr(trim($nombre), 0, 160);
    }
}
