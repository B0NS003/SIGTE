<div class="sidebar-foot">
    <button
        type="button"
        class="side-tema"
        data-sigte-tema
        aria-pressed="false"
        aria-label="Activar modo oscuro"
    >
        <span class="side-tema-icono" aria-hidden="true">
            <svg class="side-tema-svg side-tema-svg-luna" viewBox="0 0 24 24" fill="none">
                <path d="M16.4 3.2A8.8 8.8 0 1 0 20.8 14a7.2 7.2 0 0 1-4.4-10.8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            </svg>
            <svg class="side-tema-svg side-tema-svg-sol" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="3.6" stroke="currentColor" stroke-width="1.7"/>
                <path d="M12 2.8v2.1M12 19.1v2.1M2.8 12h2.1M19.1 12h2.1M5.2 5.2l1.5 1.5M17.3 17.3l1.5 1.5M17.3 6.7l1.5-1.5M5.2 18.8l1.5-1.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            </svg>
        </span>
        <span class="side-tema-copy">
            <strong class="side-tema-label">Modo oscuro</strong>
            <small class="side-tema-hint">Apagado</small>
        </span>
        <span class="side-tema-pista" aria-hidden="true">
            <span class="side-tema-knob"></span>
        </span>
    </button>

    <div class="side-user">
        <div class="avatar">{{ strtoupper(substr($usuario['nombre'], 0, 1)) }}</div>
        <div>
            <strong>{{ $usuario['nombre'] }}</strong>
            <small>{{ $usuario['rol'] }}</small>
        </div>
    </div>
    @include('partials.logout')
</div>
