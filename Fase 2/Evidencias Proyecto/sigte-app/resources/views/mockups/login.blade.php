@extends('layouts.app')

@section('title', 'SIGTE — Iniciar sesión')

@section('content')
<div class="auth">
    <section class="auth-form">
        <header class="auth-header">
            <img class="auth-logo-top" src="{{ asset('images/logo-hospital.png') }}" alt="Hospital San José Melipilla">
            <p class="auth-pill-inline">Servicio de esterilización</p>
            <p class="auth-product">SIG<span>TE</span></p>
            <p class="auth-tagline">Trazabilidad del instrumental, de recepción a entrega.</p>
        </header>

        <div class="auth-card">
            <h1 class="auth-login-title">Iniciar sesión</h1>
            <p class="hint">Usa tu cuenta institucional del hospital.</p>

            <label class="field-label" for="email">Correo</label>
            <label class="field">
                <span class="icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/></svg>
                </span>
                <input id="email" name="email" type="email" placeholder="nombre@hospital.cl" value="demo@sigte.local" autocomplete="username">
            </label>

            <label class="field-label" for="password">Contraseña</label>
            <label class="field">
                <span class="icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                </span>
                <input id="password" name="password" type="password" placeholder="••••••••" value="demo" autocomplete="current-password">
            </label>

            <label class="field-label" for="rol">Rol</label>
            <label class="field field-select">
                <select id="rol" name="rol">
                    <option value="administradora">Administradora</option>
                    <option value="enfermera">Enfermera de turno</option>
                    <option value="operador" selected>Operadora</option>
                </select>
            </label>

            <button class="btn btn-primary" type="button" id="btn-ingresar">Ingresar</button>
        </div>
    </section>

    <aside class="auth-visual" role="img" aria-label="Fachada del Hospital San José de Melipilla">
        <div class="auth-visual-caption">
            <strong>Hospital San José de Melipilla</strong>
            <span>Menos papel. Más control del ciclo de esterilización.</span>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btn-ingresar')?.addEventListener('click', function () {
  var rol = document.getElementById('rol')?.value || 'operador';
  window.location.href = @json(url('/panel')) + '/' + rol;
});
</script>
@endpush