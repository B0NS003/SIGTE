<?php
// Clasifica cada selector de flujo.css según las vistas donde aparecen sus clases e ids.
// Uso: php mapa-flujo.php <raiz-de-sigte-app>
$raiz = $argv[1] ?? getcwd();
$leer = fn (string $p) => file_get_contents($raiz.'/'.$p);

$comunes = $leer('resources/views/layouts/app.blade.php')
    .$leer('public/js/sigte-controles.js')
    .$leer('public/js/sigte-tema.js');
foreach (glob($raiz.'/resources/views/partials/*') as $f) {
    $comunes .= file_get_contents($f);
}
foreach (glob($raiz.'/resources/views/layouts/partials/*') as $f) {
    $comunes .= file_get_contents($f);
}

$vistas = [
    'panel' => $leer('resources/views/flujo/panel.blade.php')
        .$leer('resources/views/flujo/partials/modales-flujo.blade.php')
        .$leer('resources/views/flujo/partials/tarjeta-caja.blade.php')
        .$leer('resources/views/flujo/partials/puesto.blade.php')
        .$leer('resources/views/flujo/partials/proceso-reloj.blade.php')
        .$leer('resources/views/flujo/partials/track-anim.blade.php'),
    'recepcion' => $leer('resources/views/flujo/recepcion.blade.php')
        .$leer('resources/views/flujo/partials/puesto.blade.php'),
    'entrega' => $leer('resources/views/flujo/entrega.blade.php')
        .$leer('resources/views/flujo/partials/puesto.blade.php'),
    'legado' => $leer('resources/views/flujo/avanzar.blade.php')
        .$leer('resources/views/flujo/actividad.blade.php'),
];
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
            $res[] = ['tipo' => 'comentario', 'texto' => substr($texto, $i, $f - $i)];
            $i = $f;
            continue;
        }
        $llave = strpos($texto, '{', $i);
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
    foreach ($propios as $n) {
        if (str_starts_with($n, 'ops-etapa--') && ! in_array('panel', $r, true)) {
            $r[] = 'panel';
        }
    }

    return $r;
}

$css = file_get_contents($raiz.'/public/css/flujo.css');
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
            $vistas = vistasDe($s, $enComun, $enVista);
            $filas[] = ['linea' => $linea, 'sel' => $s, 'vistas' => $vistas, 'media' => $media];
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
    $lineas[] = str_pad((string) $f['linea'], 5, ' ', STR_PAD_LEFT).' '.str_pad($v, 28).' '.$media.$f['sel'];
}
file_put_contents(__DIR__.'/mapa-flujo.txt', implode("\n", $lineas)."\n");
