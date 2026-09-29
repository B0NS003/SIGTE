@extends('layouts.app')

@section('title', 'SIGTE - Iniciar sesion')

@section('content')
<div class="auth">
    <section class="auth-form">
        <p class="auth-brand">SIGTE</p>
        <p class="auth-sub">Sistema Integral de Gestion y Trazabilidad de Esterilizacion · Hospital San Jose de Melipilla</p>
        <h1>Iniciar sesion</h1>
        <p class="hint">Ingresa con tu cuenta institucional.</p>

        @if ($errors->any())
            <div style="background:#fee2e2;color:#991b1b;border-radius:12px;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.9rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label class="field">
                <span class="icon" aria-hidden="true">*</span>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Correo electronico" autofocus>
            </label>
            <label class="field">
                <span class="icon" aria-hidden="true">*</span>
                <input type="password" name="password" placeholder="Contrasena">
            </label>
            <button class="btn btn-primary" type="submit">INICIAR SESION</button>
        </form>
        <div class="auth-links">
            <p style="margin:0.75rem 0 0;color:var(--muted);font-size:0.85rem;">
                Demo: tecnico@sigte.local / password · jefatura@sigte.local / password
            </p>
        </div>
    </section>
    <aside class="auth-visual" role="img" aria-label="Hospital San Jose de Melipilla">
        <div class="auth-visual-caption">
            <strong>Hospital San Jose de Melipilla</strong>
            <span>Trazabilidad del material desde recepcion hasta entrega, en un solo lugar.</span>
        </div>
    </aside>
</div>
@endsection