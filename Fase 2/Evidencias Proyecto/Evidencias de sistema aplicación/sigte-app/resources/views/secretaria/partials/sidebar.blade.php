<aside class="sidebar">
    <div class="logo-wrap">
        <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
        <div>
            <div class="logo">SIG<span>TE</span></div>
            <div class="logo-sub">Secretaría</div>
        </div>
    </div>

    @include('partials.menu-rol')

    <div class="sidebar-foot">
        <div class="side-user">
            <div class="avatar">{{ strtoupper(substr($usuario['nombre'], 0, 1)) }}</div>
            <div>
                <strong>{{ $usuario['nombre'] }}</strong>
                <small>{{ $usuario['rol'] }}</small>
            </div>
        </div>
        @include('partials.logout')
    </div>
</aside>
