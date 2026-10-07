@php
    $icono = $icono ?? 'inicio';
@endphp
<svg class="sigte-icon nav-icon" viewBox="0 0 24 24" aria-hidden="true">
@switch($icono)
    @case('flujo')
        <circle cx="5.5" cy="12" r="2" fill="none" stroke="currentColor" stroke-width="1.75"/>
        <circle cx="12" cy="12" r="2" fill="none" stroke="currentColor" stroke-width="1.75"/>
        <circle cx="18.5" cy="12" r="2" fill="none" stroke="currentColor" stroke-width="1.75"/>
        <path d="M7.5 12h2.5M14 12h2.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        @break
    @case('recepcion')
        <path d="M4 13.2V18a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 20 18v-4.8" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        <path d="M4 13.2 6.3 7.2A1.6 1.6 0 0 1 7.8 6.2h8.4a1.6 1.6 0 0 1 1.5 1l2.3 6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        <path d="M4 13.2h4.1a2 2 0 0 0 1.9 1.3h4a2 2 0 0 0 1.9-1.3H20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        @break
    @case('entrega')
        <path d="M4.5 12h11.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        <path d="M12.5 7.5 17.5 12l-5 4.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M4.5 7.5v9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        @break
    @case('alerta')
        <path d="M12 4.5 20.5 19.5h-17L12 4.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        <path d="M12 10v4.2" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        <path d="M12 17.2h.01" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"/>
        @break
    @case('cierre')
        <circle cx="12" cy="12" r="7.25" fill="none" stroke="currentColor" stroke-width="1.75"/>
        <path d="M12 8.2V12l2.6 1.7" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        @break
    @case('catalogo')
        <path d="M6.5 5.5h11A1.5 1.5 0 0 1 19 7v12.5H5V7A1.5 1.5 0 0 1 6.5 5.5z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        <path d="M9 9.5h6M9 13h6M9 16.5h4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        @break
    @case('inventario')
        <path d="M4.5 8.2 12 4.8l7.5 3.4v7.6L12 19.2 4.5 15.8V8.2z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        <path d="M12 12.2v7M4.5 8.2 12 12.2l7.5-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        @break
    @case('usuarios')
        <circle cx="9" cy="9" r="2.6" fill="none" stroke="currentColor" stroke-width="1.75"/>
        <circle cx="16.2" cy="10" r="2.1" fill="none" stroke="currentColor" stroke-width="1.75"/>
        <path d="M4.5 18.2c1.2-2.4 3.1-3.6 4.5-3.6s3.3 1.2 4.5 3.6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        <path d="M14.2 14.8c1.1-.5 2.4-.5 3.8.4 1 .7 1.7 1.8 2 2.9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        @break
    @case('reportes')
        <path d="M5.5 18.5V11M11 18.5V7.5M16.5 18.5v-4.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        <path d="M4.5 18.5h15" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        @break
    @case('historial')
        <path d="M12 6.5v5.2l3.2 1.9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M19.2 12a7.2 7.2 0 1 1-2.1-5.1" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        <path d="M19.2 5.2v3.4h-3.4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        @break
    @case('custodia')
        <path d="M12 4.8 18.2 7.2v4.6c0 3.7-2.5 6.4-6.2 7.6-3.7-1.2-6.2-3.9-6.2-7.6V7.2L12 4.8z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        <path d="M9.6 12.1 11.3 13.8 14.6 10.4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        @break
    @case('produccion')
        <path d="M12 4.8c3.4 4.1 5.2 7 5.2 9.4a5.2 5.2 0 1 1-10.4 0c0-2.4 1.8-5.3 5.2-9.4z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        @break
    @case('insumos')
        <path d="M4.5 9.2 12 5.5l7.5 3.7v8.3L12 21l-7.5-3.8V9.2z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        <path d="M12 12.4V21M4.5 9.2 12 12.4l7.5-3.2" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
        @break
    @default
        <path d="M5 11.2 12 5.8l7 5.4V19a1.2 1.2 0 0 1-1.2 1.2h-3.6v-4.2h-4.4V20.2H6.2A1.2 1.2 0 0 1 5 19v-7.8z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
@endswitch
</svg>
