@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/flujo.css') }}?v={{ @filemtime(public_path('css/flujo.css')) }}">
@endpush
@section('title', 'SIGTE — Nueva recepción')
@section('content')
<div class="shell shell-ops">
  <aside class="sidebar">
    <div class="logo-wrap"><img src="{{ asset('images/logo-hospital-circular.png') }}" alt="Logo hospital"><div><div class="logo">SIG<span>TE</span></div><div class="logo-sub">Operación</div></div></div>
    @include('partials.menu-rol')
    <div class="sidebar-foot"><div class="side-user"><div class="avatar">{{ strtoupper(substr($usuario['nombre'], 0, 1)) }}</div><div><strong>{{ $usuario['nombre'] }}</strong><small>{{ $usuario['rol'] }}</small></div></div>@include('partials.logout')</div>
  </aside>
  <main class="main">
    <div class="main-top"><div><p class="eyebrow">Área sucia · recepción</p><h1>Nueva recepción</h1><p class="main-sub">Selecciona el servicio, tipo de conjunto y caja desde el catálogo. Luego registra quién entrega el material.</p></div><div class="main-actions"><a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Volver al flujo</a></div></div>
    @if (session('recepcion_ok'))
      <section class="recv-notificacion recv-notificacion-exito" role="status" aria-live="polite">
        <div class="recv-notificacion-icono" aria-hidden="true">✓</div>
        <div class="recv-notificacion-contenido">
          <strong>Recepción registrada correctamente</strong>
          <p>{{ session('recepcion_ok') }}</p>
          <p>La caja ya debe aparecer en el flujo de cajas, en la etapa Recepción.</p>
          <a class="recv-notificacion-enlace" href="{{ route('mockups.panel', 'operador') }}">Ver caja en el flujo →</a>
        </div>
      </section>
    @endif
    @if ($errors->any())
      <section class="recv-notificacion recv-notificacion-error" role="alert">
        <div class="recv-notificacion-icono" aria-hidden="true">!</div>
        <div class="recv-notificacion-contenido">
          <strong>No se pudo registrar la recepción</strong>
          <p>No se guardó ninguna caja. Revisa lo siguiente e inténtalo nuevamente:</p>
          <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
          @if ($errors->has('puesto'))
            <a class="recv-notificacion-enlace" href="{{ route('mockups.panel', 'operador') }}">Ir al flujo y seleccionar responsable del puesto →</a>
          @endif
        </div>
      </section>
    @endif
    <div class="recv-layout recv-catalogo-layout">
      <form class="recv-form panel recv-catalogo-form" id="recepcion-form" method="post" action="{{ route('mockups.recepcion.guardar') }}">
        @csrf
        <div class="panel-head"><h2>Selecciona la caja</h2><span class="badge" id="recv-paso" aria-live="polite">Paso 1 de 4</span></div>
        <div class="recv-progreso"><span id="recv-progreso-barra"></span></div>
        <div class="recv-campo" id="recv-grupo-servicio"><label class="field-label" for="recv-servicio"><span class="recv-num">1</span> Servicio de origen <span class="recv-obligatorio">*</span></label><select id="recv-servicio" data-sigte-lista="1" name="servicio_id" class="recv-select" required><option value="">Selecciona un servicio...</option>@foreach ($servicios as $servicio)<option value="{{ $servicio->id }}" @selected(old('servicio_id') == $servicio->id)>{{ $servicio->nombre }}</option>@endforeach</select><small>Selecciona el servicio desde el que llega el material.</small></div>
        <div class="recv-campo is-bloqueado" id="recv-grupo-tipo"><label class="field-label" for="recv-tipo"><span class="recv-num">2</span> Tipo de conjunto <span class="recv-obligatorio">*</span></label><select id="recv-tipo" data-sigte-lista="1" name="tipo_conjunto_id" class="recv-select" required disabled><option value="">Primero selecciona el servicio...</option></select></div>
        <div class="recv-campo is-bloqueado" id="recv-grupo-ficha"><label class="field-label" for="recv-ficha"><span class="recv-num">3</span> Caja / Set <span class="recv-obligatorio">*</span></label><select id="recv-ficha" data-sigte-lista="1" name="ficha_id" class="recv-select" required disabled><option value="">Primero selecciona el tipo de conjunto...</option></select><div class="recv-caja-opciones" id="recv-caja-opciones" hidden></div></div>
        <div class="recv-campo is-bloqueado" id="recv-grupo-entrega"><label class="field-label" for="recv-quien"><span class="recv-num">4</span> Quién entrega el material <span class="recv-obligatorio">*</span></label><input id="recv-quien" name="quien_entrega" class="recv-input" type="text" maxlength="150" placeholder="Nombre de la persona que entrega el material" value="{{ old('quien_entrega') }}" autocomplete="off" required disabled><small>Escribe el nombre de la persona del servicio de origen; no necesita tener una cuenta en SIGTE.</small></div>
        <div class="recv-campo recv-adicional" id="recv-grupo-obs"><label class="field-label" for="recv-obs">Observación (opcional)</label><textarea id="recv-obs" name="obs" class="recv-textarea" rows="3" maxlength="1000" placeholder="Ej.: material incompleto, contenedor dañado...">{{ old('obs') }}</textarea></div>
        <div class="recv-actions"><a class="btn btn-ghost" href="{{ route('mockups.panel', 'operador') }}">Cancelar</a><button class="btn btn-primary recv-guardar" id="recv-guardar" type="submit" disabled>Registrar recepción</button></div>
      </form>
      <aside class="recv-side"><section class="panel recv-resumen"><div class="panel-head"><h2>Información de la caja seleccionada</h2></div><div id="recv-resumen-vacio" class="recv-resumen-vacio"><div class="recv-cubo">▧</div><strong>Selecciona una caja para ver sus datos</strong><p>Se mostrarán el código, nombre, tipo de conjunto, piezas y guía visual.</p></div><div id="recv-resumen-datos" hidden><div class="recv-resumen-titulo" id="recv-nombre"></div><dl><div><dt>Código</dt><dd id="recv-codigo"></dd></div><div><dt>Servicio</dt><dd id="recv-servicio-nombre"></dd></div><div><dt>Tipo de conjunto</dt><dd id="recv-tipo-nombre"></dd></div><div><dt>Total de piezas</dt><dd id="recv-piezas"></dd></div><div><dt>Guía visual</dt><dd id="recv-guia"></dd></div></dl></div></section><section class="panel recv-resumen"><div class="panel-head"><h2>Registro de recepción</h2></div><p class="recv-help">La caja ingresará al flujo en la etapa <strong>Recepción</strong>. El nombre de quien entrega y las observaciones quedarán registrados en la actividad de la caja.</p><p class="recv-help" style="margin-top:12px">El personal de esterilización responsable se identifica con la cuenta activa del puesto.</p></section></aside>
    </div>
  </main>
</div>
@php
    $tiposJson = $tiposConjunto->map(fn ($t) => ['id' => $t->id, 'servicio_id' => $t->servicio_id, 'nombre' => $t->nombre_conjunto])->values();
    $fichasJson = $fichasCatalogo->map(fn ($f) => ['id' => $f->id, 'codigo' => $f->codigo, 'nombre' => $f->nombre, 'servicio_id' => $f->servicio_id, 'tipo_conjunto_id' => $f->tipo_conjunto_id, 'piezas' => (int) $f->piezas, 'guia' => (bool) $f->guia_visual])->values();
@endphp
<script type="application/json" id="recv-datos-tipos">@json($tiposJson)</script>
<script type="application/json" id="recv-datos-fichas">@json($fichasJson)</script>
<script>
(function () {
  const tipos = JSON.parse(document.getElementById('recv-datos-tipos').textContent);
  const fichas = JSON.parse(document.getElementById('recv-datos-fichas').textContent);
  const servicio = document.getElementById('recv-servicio'), tipo = document.getElementById('recv-tipo'), ficha = document.getElementById('recv-ficha'), quien = document.getElementById('recv-quien');
  const opciones = document.getElementById('recv-caja-opciones'), guardar = document.getElementById('recv-guardar');
  const antiguo = {tipo: @json(old('tipo_conjunto_id')), ficha: @json(old('ficha_id'))};
  function llenar(select, datos, texto, etiqueta) {
    select.replaceChildren(new Option(texto, ''));
    datos.forEach(d => select.add(new Option(etiqueta(d), String(d.id))));
  }
  function cambiarServicio() {
    const id = Number(servicio.value);
    llenar(tipo, tipos.filter(t => Number(t.servicio_id) === id), id ? 'Selecciona un tipo de conjunto...' : 'Primero selecciona el servicio...', t => t.nombre);
    tipo.disabled = !id || tipo.options.length < 2;
    cambiarTipo();
  }
  function cambiarTipo() {
    const id = Number(tipo.value), sid = Number(servicio.value);
    llenar(ficha, fichas.filter(f => Number(f.tipo_conjunto_id) === id && Number(f.servicio_id) === sid), id ? 'Selecciona una caja del catálogo...' : 'Primero selecciona el tipo de conjunto...', f => f.codigo + ' — ' + f.nombre + ' (' + f.piezas + ' piezas)');
    ficha.disabled = !id || ficha.options.length < 2;
    cambiarFicha();
  }
  function cambiarFicha() {
    const f = fichas.find(x => String(x.id) === ficha.value);
    quien.disabled = !f;
    document.getElementById('recv-resumen-vacio').hidden = !!f;
    document.getElementById('recv-resumen-datos').hidden = !f;
    if (f) {
      const t = tipos.find(x => String(x.id) === String(f.tipo_conjunto_id));
      const s = servicio.options[servicio.selectedIndex];
      for (const [id, valor] of Object.entries({'recv-nombre':f.nombre,'recv-codigo':f.codigo,'recv-servicio-nombre':s.text,'recv-tipo-nombre':t?.nombre || '—','recv-piezas':f.piezas,'recv-guia':f.guia ? 'Disponible' : 'Sin guía visual'})) document.getElementById(id).textContent = valor;
    }
    actualizar();
  }
  function actualizar() {
    const paso = !servicio.value ? 1 : !tipo.value ? 2 : !ficha.value ? 3 : 4;
    document.getElementById('recv-paso').textContent = 'Paso ' + paso + ' de 4';
    document.getElementById('recv-progreso-barra').style.width = (paso * 25) + '%';
    [['recv-grupo-tipo',!servicio.value],['recv-grupo-ficha',!tipo.value],['recv-grupo-entrega',!ficha.value]].forEach(([id,b]) => document.getElementById(id).classList.toggle('is-bloqueado',b));
    guardar.disabled = !servicio.value || !tipo.value || !ficha.value || !quien.value.trim();
  }
  servicio.addEventListener('change', cambiarServicio);
  tipo.addEventListener('change', cambiarTipo);
  ficha.addEventListener('change', cambiarFicha);
  quien.addEventListener('input', actualizar);
  cambiarServicio();
  if (antiguo.tipo) { tipo.value = String(antiguo.tipo); cambiarTipo(); }
  if (antiguo.ficha) { ficha.value = String(antiguo.ficha); cambiarFicha(); }
  actualizar();
})();
</script>
@endsection
