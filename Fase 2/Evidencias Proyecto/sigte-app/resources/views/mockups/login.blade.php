@extends('layouts.app')

@section('title', 'SIGTE — Iniciar sesión')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

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

            @if ($errors->has('login'))
                <p class="auth-error" role="alert">{{ $errors->first('login') }}</p>
            @endif

            <form method="POST" action="{{ route('login') }}" id="form-login" novalidate>
                @csrf

                <label class="field-label" for="email">Correo</label>
                <label class="field {{ $errors->hasAny(['email', 'login']) ? 'is-invalid' : '' }}">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/></svg>
                    </span>
                    <input id="email" name="email" type="email" placeholder="nombre@hospital.cl" value="{{ old('email') }}" autocomplete="username" required @if($errors->hasAny(['email', 'login'])) aria-invalid="true" @endif @if($errors->has('email')) aria-describedby="email-error" @endif>
                </label>
                <p class="field-error" id="email-error" @unless($errors->has('email')) hidden @endunless>{{ $errors->first('email') }}</p>

                <label class="field-label" for="password">Contraseña</label>
                <label class="field {{ $errors->hasAny(['password', 'login']) ? 'is-invalid' : '' }}">
                    <span class="icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                    </span>
                    <input id="password" name="password" type="password" placeholder="••••••••" autocomplete="current-password" required @if($errors->hasAny(['password', 'login'])) aria-invalid="true" @endif @if($errors->has('password')) aria-describedby="password-error" @endif>
                    <button class="btn-ver-clave" type="button" id="toggle-password" aria-label="Mostrar contraseña" aria-pressed="false">
                        <svg class="icono-ojo" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path class="parpado" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                            <path class="cerrado" d="M4 12.5h16"/>
                            <circle class="pupila" cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </label>
                <p class="field-error" id="password-error" @unless($errors->has('password')) hidden @endunless>{{ $errors->first('password') }}</p>

                <button class="btn btn-primary" type="submit" id="btn-ingresar">
                    <span class="btn-spinner" aria-hidden="true"></span>
                    <span class="btn-label">Ingresar</span>
                </button>
            </form>
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
    <script src="{{ asset('js/login.js') }}"></script>
@endpush