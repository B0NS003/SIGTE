<?php
// Divide un CSS usando un mapa. Uso:
// php procesar-modulo.php <css> <mapa.txt> <destino> <informe.txt> <vista1,vista2,...> [cabecera_comun]
// Las vistas listadas generan archivo propio; el resto (incl. NINGUNA/legado/global/at) va a comun.css.
// Si solo hay una vista, todo vivo va a esa vista (sin comun), excepto keyframes que van con ella.
[$origen, $mapaPath, $destino, $informePath, $vistasCsv] = [$argv[1], $argv[2], $argv[3], $argv[4], $argv[5]];
$vistasPropias = array_values(array_filter(array_map('trim', explode(',', $vistasCsv))));
$tituloComun = $argv[6] ?? 'Piezas compartidas del módulo.';

$css = file_get_contents($origen);
$mapa = [];
foreach (file($mapaPath, FILE_IGNORE_NEW_LINES) as $l) {
    if (preg_match('/^\s*(\d+) (\S+)\s+(?:\[([^\]]*)\] )?(.*)$/', $l, $m)) {
        $media = preg_replace('/\s+/', ' ', $m[3] ?? '');
        $sel = preg_replace('/\s+/', ' ', $m[4]);
        $mapa[$media.'|'.$sel] = $m[2];
    }
}

function bloques(string $texto): array
{
    $res = [];
    $i = 0;
    $n = strlen($texto);
    while ($i < $n) {
        if (ctype_space($texto[$i])) {
            $i++;
            continue;
        }
        if (str_starts_with(substr($texto, $i), '/*')) {
            $f = strpos($texto, '*/', $i) + 2;
            $res[] = ['tipo' => 'comentario', 'texto' => substr($texto, $i, $f - $i)];
            $i = $f;
            continue;
        }
        $llave = strpos($texto, '{', $i);
        if ($llave === false) {
            break;
        }
        $cabecera = trim(substr($texto, $i, $llave - $i));
        $prof = 0;
        for ($j = $llave; $j < $n; $j++) {
            if ($texto[$j] === '{') {
                $prof++;
            } elseif ($texto[$j] === '}') {
                $prof--;
                if ($prof === 0) {
                    break;
                }
            }
        }
        $res[] = ['tipo' => str_starts_with($cabecera, '@') ? 'at' : 'regla', 'cabecera' => $cabecera, 'cuerpo' => substr($texto, $llave + 1, $j - $llave - 1)];
        $i = $j + 1;
    }

    return $res;
}

function partir(string $s, string $sep): array
{
    $out = [];
    $prof = 0;
    $act = '';
    $comilla = null;
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $ch = $s[$i];
        if ($comilla !== null) {
            if ($ch === $comilla) {
                $comilla = null;
            }
            $act .= $ch;
            continue;
        }
        if ($ch === '"' || $ch === "'") {
            $comilla = $ch;
        }
        if ($ch === '(' || $ch === '[') {
            $prof++;
        }
        if ($ch === ')' || $ch === ']') {
            $prof--;
        }
        if ($ch === $sep && $prof === 0) {
            $out[] = trim($act);
            $act = '';
        } else {
            $act .= $ch;
        }
    }
    if (trim($act) !== '') {
        $out[] = trim($act);
    }

    return $out;
}

$reglas = [];
$keyframes = [];
$seccion = null;
$recorrer = function (array $lista, string $media) use (&$recorrer, &$reglas, &$keyframes, &$seccion) {
    foreach ($lista as $b) {
        if ($b['tipo'] === 'comentario') {
            $seccion = $b['texto'];
            continue;
        }
        if ($b['tipo'] === 'at') {
            $cab = preg_replace('/\s+/', ' ', $b['cabecera']);
            if (str_starts_with($cab, '@keyframes')) {
                $keyframes[] = $cab." {\n".$b['cuerpo']."\n}";
                $seccion = null;
                continue;
            }
            $recorrer(bloques($b['cuerpo']), $cab);
            continue;
        }
        $decl = [];
        foreach (partir($b['cuerpo'], ';') as $d) {
            $k = strpos($d, ':');
            if ($k === false) {
                continue;
            }
            $valor = trim(substr($d, $k + 1));
            $imp = (bool) preg_match('/!important\s*$/', $valor);
            if ($imp) {
                $valor = preg_replace('/\s*!important\s*$/', '', $valor);
            }
            $decl[] = ['prop' => strtolower(trim(substr($d, 0, $k))), 'valor' => $valor, 'imp' => $imp];
        }
        $reglas[] = [
            'media' => $media,
            'selectores' => array_map(fn ($s) => preg_replace('/\s+/', ' ', $s), partir($b['cabecera'], ',')),
            'decl' => $decl,
            'seccion' => $seccion,
        ];
        $seccion = null;
    }
};
$recorrer(bloques($css), '');

$vistaDe = function (string $media, string $s) use ($mapa): string {
    $clave = $media.'|'.$s;
    if (! isset($mapa[$clave])) {
        fwrite(STDERR, "Selector sin clasificar: $media $s\n");
        exit(1);
    }

    return $mapa[$clave];
};

$informe = ['muertos' => [], 'pisadas' => []];
foreach ($reglas as &$r) {
    $vivos = [];
    foreach ($r['selectores'] as $s) {
        if ($vistaDe($r['media'], $s) === 'NINGUNA') {
            $informe['muertos'][] = $s;
        } else {
            $vivos[] = $s;
        }
    }
    $r['selectores'] = $vivos;
}
unset($r);

$cubre = [
    'padding' => '/^padding-(top|right|bottom|left)$/',
    'margin' => '/^margin-(top|right|bottom|left)$/',
    'background' => '/^background-/',
    'border' => '/^border-(top|right|bottom|left)(-(width|style|color))?$|^border-(width|style|color)$/',
    'border-radius' => '/^border-(top|bottom)-(left|right)-radius$/',
    'gap' => '/^(row|column)-gap$/',
    'flex' => '/^flex-(grow|shrink|basis)$/',
    'inset' => '/^(top|right|bottom|left)$/',
    'overflow' => '/^overflow-(x|y)$/',
    'font' => '/^font-(size|weight|style|family|variant)$|^line-height$/',
];
$pisa = function (string $posterior, string $anterior) use ($cubre): bool {
    return $posterior === $anterior || (isset($cubre[$posterior]) && preg_match($cubre[$posterior], $anterior));
};
foreach ($reglas as $i => &$r) {
    if ($r['selectores'] === []) {
        continue;
    }
    $r['decl'] = array_values(array_filter($r['decl'], function ($d) use ($i, $r, $reglas, $pisa, &$informe) {
        $cubierta = true;
        foreach ($r['selectores'] as $s) {
            $hay = false;
            for ($j = $i + 1, $n = count($reglas); $j < $n; $j++) {
                $o = $reglas[$j];
                if (($o['media'] === $r['media'] || $o['media'] === '') && in_array($s, $o['selectores'], true)) {
                    foreach ($o['decl'] as $od) {
                        if ($pisa($od['prop'], $d['prop']) && ($od['imp'] || ! $d['imp'])) {
                            $hay = true;
                            break 2;
                        }
                    }
                }
            }
            if (! $hay) {
                $cubierta = false;
                break;
            }
        }
        if ($cubierta) {
            $informe['pisadas'][] = implode(', ', $r['selectores']).($r['media'] !== '' ? ' ['.$r['media'].']' : '').' → '.$d['prop'];
        }

        return ! $cubierta;
    }));
}
unset($r);

$unaSola = count($vistasPropias) === 1;
$archivoDe = function (string $etiqueta) use ($vistasPropias, $unaSola): string {
    $partes = explode('+', $etiqueta);
    $propias = array_values(array_intersect($partes, $vistasPropias));
    if ($unaSola) {
        return $vistasPropias[0];
    }
    if (count($propias) === 1) {
        return $propias[0];
    }

    return 'comun';
};

$archivos = $unaSola ? [$vistasPropias[0] => []] : array_merge(['comun' => []], array_fill_keys($vistasPropias, []));
$salida = $archivos;
$ultima = [];
$seccionActual = null;
foreach ($reglas as $r) {
    if ($r['seccion']) {
        $seccionActual = $r['seccion'];
    }
    if ($r['selectores'] === [] || $r['decl'] === []) {
        continue;
    }
    $grupos = [];
    foreach ($r['selectores'] as $s) {
        $grupos[$archivoDe($vistaDe($r['media'], $s))][] = $s;
    }
    foreach ($grupos as $k => $selectores) {
        $lista = &$salida[$k];
        $cuerpo = function (string $sangria) use ($selectores, $r): string {
            $decl = implode("\n", array_map(fn ($d) => $sangria.'    '.$d['prop'].': '.$d['valor'].($d['imp'] ? ' !important' : '').';', $r['decl']));

            return implode(",\n", array_map(fn ($s) => $sangria.$s, $selectores))." {\n".$decl."\n".$sangria.'}';
        };
        if ($seccionActual && ($ultima[$k] ?? null) !== $seccionActual) {
            $lista[] = ['comentario' => $seccionActual];
            $ultima[$k] = $seccionActual;
        }
        $previo = $lista[count($lista) - 1] ?? null;
        if ($r['media'] !== '' && $previo && ($previo['media'] ?? null) === $r['media']) {
            $lista[count($lista) - 1]['reglas'][] = $cuerpo('    ');
        } elseif ($r['media'] !== '') {
            $lista[] = ['media' => $r['media'], 'reglas' => [$cuerpo('    ')]];
        } else {
            $lista[] = ['texto' => $cuerpo('')];
        }
        unset($lista);
    }
}

$destinoKeyframes = $unaSola ? $vistasPropias[0] : 'comun';
if ($keyframes !== []) {
    array_unshift($salida[$destinoKeyframes], ['texto' => implode("\n", $keyframes)]);
}

if (! is_dir($destino)) {
    mkdir($destino, 0777, true);
}
foreach ($salida as $k => $items) {
    if ($items === []) {
        continue;
    }
    $cab = $k === 'comun'
        ? "/* $tituloComun */"
        : "/* Vista $k. Se carga después de comun.css si existe. */";
    if ($unaSola) {
        $cab = "/* Módulo $k. */";
    }
    $texto = implode("\n", array_map(function ($it) {
        if (isset($it['comentario'])) {
            return "\n".$it['comentario'];
        }
        if (isset($it['media'])) {
            return $it['media']." {\n".implode("\n", $it['reglas'])."\n}";
        }

        return $it['texto'];
    }, $items));
    file_put_contents($destino.'/'.$k.'.css', $cab."\n".ltrim($texto, "\n")."\n");
    echo str_pad($k, 14), count(array_filter($items, fn ($i) => ! isset($i['comentario']))), ' bloques', PHP_EOL;
}
file_put_contents($informePath, 'MUERTOS ('.count($informe['muertos']).")\n".implode("\n", $informe['muertos'])."\n\nPISADAS (".count($informe['pisadas']).")\n".implode("\n", $informe['pisadas'])."\n");
echo 'muertos ', count($informe['muertos']), ' pisadas ', count($informe['pisadas']), PHP_EOL;
