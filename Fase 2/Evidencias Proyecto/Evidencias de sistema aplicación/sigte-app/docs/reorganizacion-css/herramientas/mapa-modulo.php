<?php
// Clasifica selectores de un CSS por vista.
// Uso: php mapa-modulo.php <raiz> <css-relativo> <salida-mapa.txt> <vista=archivo[,archivo2]>...
$raiz = rtrim($argv[1], '/\\');
$cssRel = $argv[2];
$salida = $argv[3];
$vistasDef = array_slice($argv, 4);

$leer = function (string $rel) use ($raiz): string {
    $p = $raiz.'/'.str_replace('\\', '/', $rel);
    if (! is_file($p)) {
        throw new RuntimeException("No existe: $p");
    }

    return file_get_contents($p);
};

$comunes = $leer('resources/views/layouts/app.blade.php')
    .$leer('public/js/sigte-controles.js')
    .$leer('public/js/sigte-tema.js');
foreach (glob($raiz.'/resources/views/partials/*') ?: [] as $f) {
    $comunes .= file_get_contents($f);
}
foreach (glob($raiz.'/resources/views/layouts/partials/*') ?: [] as $f) {
    $comunes .= file_get_contents($f);
}

$vistas = [];
foreach ($vistasDef as $def) {
    [$nombre, $archivos] = array_pad(explode('=', $def, 2), 2, '');
    $texto = '';
    foreach (explode(',', $archivos) as $a) {
        $a = trim($a);
        if ($a === '') {
            continue;
        }
        if (str_ends_with($a, '/')) {
            foreach (glob($raiz.'/'.$a.'*') ?: [] as $f) {
                $texto .= file_get_contents($f);
            }
        } else {
            $texto .= $leer($a);
        }
    }
    $vistas[$nombre] = $texto;
}

$palabras = fn (string $t) => array_flip(array_unique(preg_match_all('/[A-Za-z][\w-]*/', $t, $m) ? $m[0] : []));
$enComun = $palabras($comunes);
$enVista = [];
foreach ($vistas as $k => $t) {
    $enVista[$k] = $palabras($t);
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
            $res[] = ['tipo' => 'comentario', 'texto' => substr($texto, $i, $f - $i), 'inicio' => $i];
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
        $res[] = [
            'tipo' => str_starts_with($cabecera, '@') ? 'at' : 'regla',
            'cabecera' => $cabecera,
            'cuerpo' => substr($texto, $llave + 1, $j - $llave - 1),
            'inicio' => $i,
        ];
        $i = $j + 1;
    }

    return $res;
}

function partir(string $s): array
{
    $out = [];
    $prof = 0;
    $act = '';
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $ch = $s[$i];
        if ($ch === '(' || $ch === '[') {
            $prof++;
        }
        if ($ch === ')' || $ch === ']') {
            $prof--;
        }
        if ($ch === ',' && $prof === 0) {
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

function vistasDe(string $selector, array $enComun, array $enVista): array
{
    $limpio = preg_replace('/\[[^\]]*\]/', '', $selector);
    preg_match_all('/[.#]([a-zA-Z][\w-]*)/', $limpio, $m);
    $nombres = $m[1];
    $propios = array_values(array_filter($nombres, fn ($n) => ! isset($enComun[$n])));
    if ($nombres === []) {
        return ['global'];
    }
    $r = [];
    foreach (array_keys($enVista) as $v) {
        $ok = true;
        foreach ($nombres as $n) {
            if (! isset($enComun[$n]) && ! isset($enVista[$v][$n])) {
                $ok = false;
                break;
            }
        }
        if ($ok) {
            $r[] = $v;
        }
    }
    if ($propios === [] && count($r) === count($enVista)) {
        return ['global'];
    }

    return $r;
}

$css = $leer($cssRel);
$filas = [];
$recorrer = function (array $lista, int $base, string $media) use (&$recorrer, &$filas, $css, $enComun, $enVista) {
    foreach ($lista as $b) {
        if ($b['tipo'] === 'at') {
            if (preg_match('/^@(media|supports)/', $b['cabecera'])) {
                $abs = $base + $b['inicio'];
                $recorrer(bloques($b['cuerpo']), $abs + strpos($css, '{', $abs) + 1 - $abs, $b['cabecera']);
            } else {
                $filas[] = ['linea' => substr_count(substr($css, 0, $base + $b['inicio']), "\n") + 1, 'sel' => $b['cabecera'], 'vistas' => ['at'], 'media' => $media];
            }
            continue;
        }
        if ($b['tipo'] !== 'regla') {
            continue;
        }
        $linea = substr_count(substr($css, 0, $base + $b['inicio']), "\n") + 1;
        foreach (partir($b['cabecera']) as $s) {
            $filas[] = ['linea' => $linea, 'sel' => $s, 'vistas' => vistasDe($s, $enComun, $enVista), 'media' => $media];
        }
    }
};
$recorrer(bloques($css), 0, '');

$resumen = [];
foreach ($filas as $f) {
    $k = $f['vistas'] === [] ? 'NINGUNA' : implode('+', $f['vistas']);
    $resumen[$k] = ($resumen[$k] ?? 0) + 1;
}
ksort($resumen);
echo json_encode($resumen, JSON_UNESCAPED_UNICODE), PHP_EOL;

$lineas = [];
foreach ($filas as $f) {
    $v = $f['vistas'] === [] ? 'NINGUNA' : implode('+', $f['vistas']);
    $media = $f['media'] !== '' ? '['.preg_replace('/\s+/', ' ', $f['media']).'] ' : '';
    $lineas[] = str_pad((string) $f['linea'], 5, ' ', STR_PAD_LEFT).' '.str_pad($v, 36).' '.$media.$f['sel'];
}
file_put_contents($salida, implode("\n", $lineas)."\n");
