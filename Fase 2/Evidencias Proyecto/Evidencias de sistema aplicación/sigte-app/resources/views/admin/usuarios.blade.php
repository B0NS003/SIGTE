@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ @filemtime(public_path('css/admin.css')) }}">
@endpush

@section('title', 'SIGTE — Usuarios')

<!-- Sección de contenido de la página de usuarios -->
@section('content')
<!-- Contenedor principal de la página -->
<div class="shell">
    <!-- Barra lateral de navegación -->
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Administración</div>
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

    <!-- Contenido principal de la página -->
    <main class="main">
        <!-- Encabezado de la página -->
        <div class="main-top">
            <div>
                <p class="eyebrow">Gestión · solo administradora</p>
                <h1>Usuarios</h1>
                <p class="main-sub">La jefatura crea las cuentas, asigna el rol y puede desactivarlas. La enfermera y la operadora no entran aquí.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'administradora') }}">Volver al resumen</a>
            </div>
        </div>

        <div class="kpi-row">
            <div class="kpi">
                <div class="l">Total</div>
                <div class="n">{{ $resumen['total'] }}</div>
                <div class="h">Cuentas registradas</div>
            </div>
            <div class="kpi ok">
                <div class="l">Activos</div>
                <div class="n">{{ $resumen['activos'] }}</div>
                <div class="h">Pueden iniciar sesión</div>
            </div>
            <div class="kpi">
                <div class="l">Inactivos</div>
                <div class="n">{{ $resumen['inactivos'] }}</div>
                <div class="h">Desactivados</div>
            </div>
            <div class="kpi">
                <div class="l">Administradoras</div>
                <div class="n">{{ $resumen['admin'] }}</div>
                <div class="h">Con gestión de usuarios</div>
            </div>
        </div>
        <!-- Grilla de dos columnas para la lista de usuarios -->
        <div class="users-page">
            <section class="panel list">
                <!-- Encabezado de la lista de usuarios -->
                <div class="panel-head">
                    <h2>Cuentas</h2>
                    <span class="badge">{{ count($usuarios) }}</span>
                </div>
                <!-- Tabla de usuarios -->
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último acceso</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Iteración sobre los usuarios -->
                            @foreach ($usuarios as $u)
                                <tr class="{{ $u['estado'] === 'inactivo' ? 'user-inactive' : '' }}" data-estado="{{ $u['estado'] }}">
                                    <td>
                                        <strong class="js-nombre">{{ $u['nombre'] }}</strong>
                                        <div class="muted-cell js-email">{{ $u['email'] }}</div>
                                    </td>
                                    <td><span class="badge rol-pill js-rol {{ $u['rol'] === 'Administradora' ? 'rol-admin' : ($u['rol'] === 'Operadora' ? 'rol-operadora' : 'rol-enfermera') }}">{{ $u['rol'] }}</span></td>
                                    <td>
                                        <span class="pill js-estado {{ $u['estado'] === 'activo' ? 'pill-ok' : 'pill-warn' }}">{{ $u['estado'] === 'activo' ? 'Activo' : 'Inactivo' }}</span>
                                    </td>
                                    <td>{{ $u['ultimo'] }}</td>
                                    <td class="user-actions">
                                        <button class="btn btn-ghost js-editar" type="button">Editar</button>
                                        <button class="btn {{ $u['estado'] === 'activo' ? 'btn-ghost' : 'btn-dark' }} js-estado-btn" type="button">{{ $u['estado'] === 'activo' ? 'Desactivar' : 'Reactivar' }}</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <form class="panel cuenta-alta" id="cuenta-form" action="{{ route('mockups.usuarios') }}" method="get" onsubmit="return false">
                <div class="panel-head">
                    <h2 id="cuenta-titulo">Nueva cuenta</h2>
                    <span class="badge" id="cuenta-modo">Alta</span>
                </div>
                <p class="adv-ok" id="cuenta-ok" role="status" hidden></p>
                <p class="auth-error" id="cuenta-error" role="alert" hidden></p>
                <div class="cuenta-grid">
                    <div>
                        <label class="field-label" for="nombre">Nombre completo</label>
                        <label class="field">
                            <input id="nombre" name="nombre" type="text" placeholder="Nombre y apellido" value="">
                        </label>
                    </div>
                    <div>
                        <label class="field-label" for="email">Correo institucional</label>
                        <label class="field">
                            <input id="email" name="email" type="email" placeholder="nombre@hsjmelipilla.cl" value="">
                        </label>
                    </div>
                    <div>
                        <label class="field-label" for="rol">Rol</label>
                        <label class="field field-select">
                            <select id="rol" name="rol">
                                <option value="">Elige el rol</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="field-label" for="pass">Contraseña temporal</label>
                        <label class="field">
                            <input id="pass" name="pass" type="text" placeholder="La cambia al entrar" value="">
                        </label>
                    </div>
                </div>
                <div class="cuenta-pie">
                    <label class="check">
                        <input id="cambiar-clave" type="checkbox" checked>
                        <span>Debe cambiar contraseña en el primer ingreso</span>
                    </label>
                    <div class="recv-actions">
                        <button class="btn btn-ghost" id="cuenta-cancelar" type="button" hidden>Cancelar</button>
                        <button class="btn btn-primary" id="cuenta-guardar" type="button">Crear usuario</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
    var formFila = null;
    var titulo = document.getElementById('cuenta-titulo');
    var modo = document.getElementById('cuenta-modo');
    var guardar = document.getElementById('cuenta-guardar');
    var cancelar = document.getElementById('cuenta-cancelar');
    var ok = document.getElementById('cuenta-ok');
    var error = document.getElementById('cuenta-error');
    var nombre = document.getElementById('nombre');
    var email = document.getElementById('email');
    var rol = document.getElementById('rol');
    var pass = document.getElementById('pass');

    function claseRol(valor) {
        if (valor === 'Administradora') return 'rol-admin';
        if (valor === 'Operadora') return 'rol-operadora';
        return 'rol-enfermera';
    }

    function aviso(texto, esError) {
        ok.hidden = esError || !texto;
        error.hidden = !esError || !texto;
        (esError ? error : ok).textContent = texto || '';
    }

    function limpiarAlta() {
        formFila = null;
        titulo.textContent = 'Nueva cuenta';
        modo.textContent = 'Alta';
        guardar.textContent = 'Crear usuario';
        cancelar.hidden = true;
        nombre.value = '';
        email.value = '';
        rol.value = '';
        pass.value = '';
    }

    document.querySelector('.users-page tbody').addEventListener('click', function (evento) {
        var editar = evento.target.closest('.js-editar');
        var estadoBtn = evento.target.closest('.js-estado-btn');
        if (editar) {
            formFila = editar.closest('tr');
            nombre.value = formFila.querySelector('.js-nombre').textContent;
            email.value = formFila.querySelector('.js-email').textContent;
            rol.value = formFila.querySelector('.js-rol').textContent;
            pass.value = '';
            titulo.textContent = 'Editar cuenta';
            modo.textContent = 'Cambio';
            guardar.textContent = 'Guardar cambios';
            cancelar.hidden = false;
            aviso('', false);
            nombre.focus();
        }
        if (!estadoBtn) {
            return;
        }
        var fila = estadoBtn.closest('tr');
        var persona = fila.querySelector('.js-nombre').textContent;
        var activo = fila.dataset.estado === 'activo';
        var pill = fila.querySelector('.js-estado');
        fila.dataset.estado = activo ? 'inactivo' : 'activo';
        fila.classList.toggle('user-inactive', activo);
        pill.textContent = activo ? 'Inactivo' : 'Activo';
        pill.classList.toggle('pill-ok', !activo);
        pill.classList.toggle('pill-warn', activo);
        estadoBtn.textContent = activo ? 'Reactivar' : 'Desactivar';
        estadoBtn.classList.toggle('btn-dark', activo);
        estadoBtn.classList.toggle('btn-ghost', !activo);
        aviso(activo
            ? persona + ' quedó inactiva. No puede entrar. El historial se mantiene.'
            : persona + ' puede volver a entrar.', false);
    });

    cancelar.addEventListener('click', function () {
        limpiarAlta();
        aviso('', false);
    });

    guardar.addEventListener('click', function () {
        if (!nombre.value.trim() || !email.value.trim() || !rol.value || (formFila === null && !pass.value.trim())) {
            aviso('Completa nombre, correo, rol' + (formFila === null ? ' y contraseña temporal.' : '.'), true);
            return;
        }
        if (!email.value.includes('@')) {
            aviso('El correo institucional no es válido.', true);
            return;
        }
        if (formFila) {
            formFila.querySelector('.js-nombre').textContent = nombre.value.trim();
            formFila.querySelector('.js-email').textContent = email.value.trim();
            var marca = formFila.querySelector('.js-rol');
            marca.textContent = rol.value;
            marca.classList.remove('rol-admin', 'rol-enfermera', 'rol-operadora');
            marca.classList.add(claseRol(rol.value));
            aviso('Listo. Quedaron los datos y el rol de ' + nombre.value.trim() + '.', false);
            limpiarAlta();
            return;
        }
        var cuerpo = document.querySelector('.users-page tbody');
        var fila = document.createElement('tr');
        fila.dataset.estado = 'activo';
        fila.innerHTML = '<td><strong class="js-nombre"></strong><div class="muted-cell js-email"></div></td><td><span class="badge rol-pill js-rol"></span></td><td><span class="pill pill-ok js-estado">Activo</span></td><td>Hoy</td><td class="user-actions"><button class="btn btn-ghost js-editar" type="button">Editar</button><button class="btn btn-ghost js-estado-btn" type="button">Desactivar</button></td>';
        fila.querySelector('.js-nombre').textContent = nombre.value.trim();
        fila.querySelector('.js-email').textContent = email.value.trim();
        fila.querySelector('.js-rol').textContent = rol.value;
        fila.querySelector('.js-rol').classList.add(claseRol(rol.value));
        cuerpo.prepend(fila);
        aviso('Listo. ' + nombre.value.trim() + ' quedó como ' + rol.value + '.', false);
        limpiarAlta();
    });
</script>
@endsection
