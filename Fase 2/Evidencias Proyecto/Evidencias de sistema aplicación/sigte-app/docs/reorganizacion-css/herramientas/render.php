<?php
// Uso: php render.php <carpeta> [css-alternativo]
$carpeta = $argv[1] ?? 'despues';
$cssAlternativo = ($argv[2] ?? '-') === '-' ? null : $argv[2];
$base = '/var/www/html';
require $base.'/vendor/autoload.php';

$paginas = [
    ['login', '/login', null],
    ['panel-admin', '/panel/administradora', 'administradora'],
    ['panel-enfermera', '/panel/enfermera', 'enfermera'],
    ['panel-operador', '/panel/operador', 'operador'],
    ['panel-operador-etapa', '/panel/operador?accion=etapa', 'operador'],
    ['panel-operador-actividad', '/panel/operador?accion=actividad', 'operador'],
    ['panel-secretaria', '/panel/secretaria', 'secretaria'],
    ['recepcion', '/operador/recepcion', 'operador'],
    ['avanzar', '/operador/avanzar', 'operador'],
    ['actividad', '/operador/actividad', 'operador'],
    ['entrega', '/operador/entrega', 'operador'],
    ['catalogo', '/consulta/catalogo', 'operador'],
    ['catalogo-admin', '/admin/catalogo', 'administradora'],
    ['ficha', '/consulta/catalogo/__FICHA__', 'operador'],
    ['inventario-operador', '/consulta/inventario', 'operador'],
    ['inventario-enfermera', '/consulta/inventario', 'enfermera'],
    ['usuarios', '/admin/usuarios', 'administradora'],
    ['turnos', '/admin/turnos', 'administradora'],
    ['reportes', '/admin/reportes', 'administradora'],
    ['entregas', '/admin/entregas', 'administradora'],
    ['custodia', '/admin/custodia', 'administradora'],
    ['alertas', '/admin/alertas', 'administradora'],
    ['cierre-turno', '/enfermera/cierre-turno', 'enfermera'],
    ['sec-produccion', '/secretaria/produccion', 'secretaria'],
    ['sec-insumos', '/secretaria/insumos', 'secretaria'],
    ['sec-reportes', '/secretaria/reportes', 'secretaria'],
];

if (! empty($argv[3])) {
    $filtro = explode(',', $argv[3]);
    $paginas = array_filter($paginas, fn ($p) => in_array($p[0], $filtro, true));
}

$salida = $base.'/public/_ref/'.$carpeta;
@mkdir($salida, 0777, true);

foreach ($paginas as [$nombre, $ruta, $rol]) {
    $app = require $base.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();

    if (str_contains($ruta, '__FICHA__')) {
        $codigo = Illuminate\Support\Facades\DB::table('fichas')->value('codigo');
        $ruta = str_replace('__FICHA__', (string) $codigo, $ruta);
    }

    $peticion = Illuminate\Http\Request::create($ruta, 'GET');
    $app->instance('request', $peticion);
    if ($rol !== null) {
        $usuario = App\Models\User::whereHas('rol', fn ($q) => $q->where('nombre', $rol))->first();
        Illuminate\Support\Facades\Auth::setUser($usuario);
    }

    try {
        $respuesta = $kernel->handle($peticion);
        $html = $respuesta->getContent();
        $html = str_replace(['http://localhost:8080/', 'http://localhost/'], '/', $html);
        if ($cssAlternativo) {
            $html = str_replace('"/css/', '"/_ref/'.$cssAlternativo.'/', $html);
        }
        file_put_contents($salida.'/'.$nombre.'.html', $html);
        echo str_pad($nombre, 28), $respuesta->getStatusCode(), ' ', strlen($html), PHP_EOL;
    } catch (Throwable $e) {
        echo str_pad($nombre, 28), 'ERROR ', $e->getMessage(), PHP_EOL;
    }
}
