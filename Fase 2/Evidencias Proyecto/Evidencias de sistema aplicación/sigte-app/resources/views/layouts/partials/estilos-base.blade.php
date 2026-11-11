{{-- El orden importa: tokens, base y estructura, componentes y al final utilidades. --}}
@foreach ([
    'base/tokens',
    'base/base',
    'base/layout',
    'componentes/botones',
    'componentes/formularios',
    'componentes/pestanas',
    'componentes/paneles',
    'componentes/tablas',
    'componentes/insignias',
    'componentes/mensajes',
    'base/utilidades',
] as $hoja)
    <link rel="stylesheet" href="{{ asset('css/'.$hoja.'.css') }}?v={{ @filemtime(public_path('css/'.$hoja.'.css')) }}">
@endforeach
