@foreach (\App\Support\AccesoPorRol::menu(auth()->user()) as $grupo)
    <div class="nav-label">{{ $grupo['titulo'] }}</div>
    <nav class="nav">
        @foreach ($grupo['enlaces'] as $enlace)
            <a href="{{ $enlace['url'] }}" @class(['active' => $enlace['activo']])>
                {{ $enlace['texto'] }}
                @if ($enlace['badge'])
                    <span class="nav-badge">{{ $enlace['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>
@endforeach
