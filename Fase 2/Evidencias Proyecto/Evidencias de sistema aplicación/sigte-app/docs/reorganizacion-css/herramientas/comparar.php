<?php
// Compara dos mediciones por página, ignorando <script>.
// Uso: php comparar.php <carpeta-out> <etiquetaA> <etiquetaB>
[$carpeta, $a, $b] = [$argv[1], $argv[2], $argv[3]];

function cargar(string $carpeta, string $etiqueta, string $v): array
{
    $paginas = [];
    foreach (file($carpeta.'/'.$etiqueta.'-'.$v.'.txt', FILE_IGNORE_NEW_LINES) as $l) {
        $p = explode('|', $l);
        if (count($p) < 4 || str_starts_with($p[2], 'script')) {
            continue;
        }
        $paginas[$p[0]][] = [$p[2], $p[3]];
    }

    return $paginas;
}

foreach (['claro-1280', 'oscuro-1280', 'claro-390'] as $v) {
    $A = cargar($carpeta, $a, $v);
    $B = cargar($carpeta, $b, $v);
    $grupos = [];
    $primeros = [];
    $total = 0;
    foreach ($A as $pag => $xs) {
        $ys = $B[$pag] ?? [];
        if (count($xs) !== count($ys)) {
            echo "  $pag: ".count($xs).' vs '.count($ys)." elementos\n";
        }
        $n = max(count($xs), count($ys));
        for ($i = 0; $i < $n; $i++) {
            $x = $xs[$i] ?? ['', ''];
            $y = $ys[$i] ?? ['', ''];
            if ($x[0] === $y[0] && $x[1] === $y[1]) {
                continue;
            }
            $total++;
            $k = $x[0] === $y[0] ? "$pag {$x[0]}" : "DESALINEADO $pag {$x[0]} <> {$y[0]}";
            $grupos[$k] = ($grupos[$k] ?? 0) + 1;
            $primeros[$k] ??= $i;
        }
    }
    echo "== $v: $total distintos\n";
    arsort($grupos);
    $n = 0;
    foreach ($grupos as $k => $c) {
        if ($n++ >= 25) {
            break;
        }
        echo '  '.str_pad((string) $c, 5, ' ', STR_PAD_LEFT)."x $k (pos {$primeros[$k]})\n";
    }
}
