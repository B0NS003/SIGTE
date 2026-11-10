@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/enfermera.css') }}?v={{ @filemtime(public_path('css/enfermera.css')) }}">
@endpush

@section('title', 'SIGTE — Cierre de turno')

@section('content')
@php
    $puede_cerrar = $puede_cerrar ?? false;
    $motivo_bloqueo = $motivo_bloqueo ?? null;
    $ya_cerrado = $ya_cerrado ?? null;
    $relevos = $relevos ?? [];
    $alertas = $alertas ?? [];
    $pendientes = $pendientes ?? [];
    $resumen = $resumen ?? [];
    $historial = $historial ?? [];
    $turno = $turno ?? [
        'fecha' => now('America/Santiago')->format('d/m/Y'),
        'bloque' => '—',
        'responsable' => '—',
        'relevo' => '—',
        'operadoras' => '—',
    ];
@endphp
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo-sigte.png') }}" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Turno</div>
            </div>
        </div>
        @include('partials.menu-rol')
        @include('partials.sidebar-foot')
    </aside>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Traspaso · responsabilidad del turno</p>
                <h1>Cierre de turno</h1>
                <p class="main-sub">Revisa pendientes y alertas, deja la nota al relevo y confirma. Queda registro de quién estuvo a cargo.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="{{ route('mockups.panel', 'enfermera') }}">Volver</a>
            </div>
        </div>

        @if (session('ok'))
            <div class="ops-empty cierre-flash-ok" role="status">
                <strong>Listo</strong>
                <p>{{ session('ok') }}</p>
            </div>
        @endif

        @error('cierre')
            <div class="ops-empty cierre-flash-error" role="alert">
                <strong>No se pudo cerrar</strong>
                <p>{{ $message }}</p>
            </div>
        @enderror

        @if (! $puede_cerrar && $motivo_bloqueo)
            <div class="ops-empty cierre-flash-aviso" role="status">
                <strong>Cierre no disponible</strong>
                <p>{{ $motivo_bloqueo }}</p>
                @if ($ya_cerrado)
                    <p class="recv-help">Cerrado por {{ $ya_cerrado->responsable?->name ?? 'una enfermera' }}
                        a las {{ $ya_cerrado->cerrado_at?->timezone('America/Santiago')->format('H:i') }}.</p>
                @endif
            </div>
        @endif

        <div class="grid-2 cierre-layout">
            <form
                class="panel recv-form cierre-form"
                action="{{ route('mockups.cierre_turno.guardar') }}"
                method="post"
                id="form-cierre-turno"
                novalidate
            >
                @csrf
                <div class="panel-head">
                    <h2>Datos del cierre</h2>
                    <span class="badge">{{ $turno['fecha'] }}</span>
                </div>

                <div class="adv-summary cierre-handoff" aria-label="Traspaso de turno">
                    <div>
                        <span class="adv-k">Entrega turno</span>
                        <strong>{{ $turno['responsable'] }}</strong>
                        <small>{{ $turno['bloque'] }}</small>
                    </div>
                    <div class="adv-arrow" aria-hidden="true">→</div>
                    <div>
                        <span class="adv-k">Recibe relevo</span>
                        <strong id="relevo-vista">
                            @php
                                $relevoSel = old('relevo_user_id');
                                $relevoNombre = collect($relevos)->firstWhere('id', (int) $relevoSel)['nombre']
                                    ?? $turno['relevo'];
                            @endphp
                            {{ $relevoNombre }}
                        </strong>
                        <small>Próximo bloque</small>
                    </div>
                </div>

                <label class="field-label" for="relevo">Quién recibe el turno <span class="entrega-obligatorio" aria-hidden="true">*</span></label>
                <label class="field field-select @error('relevo_user_id') is-invalido @enderror" id="wrap-relevo">
                    <select id="relevo" name="relevo_user_id" required @disabled(! $puede_cerrar)>
                        <option value="" disabled @selected(! old('relevo_user_id'))>Selecciona quien recibe…</option>
                        @foreach ($relevos as $opcion)
                            <option
                                value="{{ $opcion['id'] }}"
                                @selected((string) old('relevo_user_id', '') === (string) $opcion['id'] || (! old('relevo_user_id') && $opcion['nombre'] === $turno['relevo']))
                            >{{ $opcion['nombre'] }}</option>
                        @endforeach
                    </select>
                </label>
                @error('relevo_user_id')
                    <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                @else
                    <p class="entrega-error-campo" id="err-relevo" hidden role="alert">Indica quién recibe el turno.</p>
                @enderror

                <section class="cierre-revision" aria-labelledby="cierre-revision-titulo">
                    <div class="cierre-revision-head">
                        <h3 id="cierre-revision-titulo">Antes de confirmar</h3>
                        <p>Esto es lo que queda abierto al cierre. El relevo debe enterarse.</p>
                    </div>

                    <div class="cierre-revision-grid">
                        <div class="cierre-bloque" data-cierre-pager data-page-size="5">
                            <div class="cierre-bloque-head">
                                <strong>Alertas abiertas</strong>
                                <span class="pill {{ count($alertas) > 0 ? 'pill-danger' : 'pill-ok' }}">{{ count($alertas) }}</span>
                            </div>
                            @if (count($alertas) === 0)
                                <p class="cierre-vacio">Sin alertas abiertas en este turno.</p>
                            @else
                                <ul class="cierre-lista">
                                    @foreach ($alertas as $alerta)
                                        <li data-cierre-item>
                                            <span class="cierre-lista-codigo">{{ $alerta['codigo'] }}</span>
                                            <span class="cierre-lista-texto">{{ $alerta['detalle'] }}</span>
                                            <span class="pill pill-danger">{{ $alerta['nivel'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="cierre-pager" hidden>
                                    <p class="cierre-pager-meta"></p>
                                    <nav class="cierre-pager-nav" aria-label="Páginas de alertas">
                                        <button type="button" class="cierre-pager-btn" data-cierre-prev aria-label="Página anterior">‹</button>
                                        <button type="button" class="cierre-pager-btn" data-cierre-next aria-label="Página siguiente">›</button>
                                    </nav>
                                </div>
                            @endif
                        </div>

                        <div class="cierre-bloque" data-cierre-pager data-page-size="5">
                            <div class="cierre-bloque-head">
                                <strong>Actividades pendientes</strong>
                                <span class="badge">{{ count($pendientes) }}</span>
                            </div>
                            @if (count($pendientes) === 0)
                                <p class="cierre-vacio">No hay pendientes registrados.</p>
                            @else
                                <ul class="cierre-lista cierre-lista-plain">
                                    @foreach ($pendientes as $p)
                                        <li data-cierre-item>
                                            <span class="cierre-lista-texto">{{ $p }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="cierre-pager" hidden>
                                    <p class="cierre-pager-meta"></p>
                                    <nav class="cierre-pager-nav" aria-label="Páginas de pendientes">
                                        <button type="button" class="cierre-pager-btn" data-cierre-prev aria-label="Página anterior">‹</button>
                                        <button type="button" class="cierre-pager-btn" data-cierre-next aria-label="Página siguiente">›</button>
                                    </nav>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <label class="field-label" for="nota">Observaciones al relevo</label>
                <textarea
                    id="nota"
                    class="recv-textarea cierre-nota"
                    name="nota"
                    rows="5"
                    maxlength="1000"
                    placeholder="Agrega contexto que no esté en la lista (opcional, recomendado si hay alertas)."
                    @disabled(! $puede_cerrar)
                >{{ old('nota') }}</textarea>

                <fieldset class="cierre-checks @error('check_alertas') is-invalido @enderror @error('check_criticas') is-invalido @enderror" id="wrap-checks" @disabled(! $puede_cerrar)>
                    <legend class="field-label">Checklist de traspaso <span class="entrega-obligatorio" aria-hidden="true">*</span></legend>
                    <div class="recv-checks">
                        <label class="check">
                            <input type="checkbox" name="check_alertas" value="1" required @checked(old('check_alertas')) @disabled(! $puede_cerrar)>
                            <span>Revisé las alertas abiertas con el equipo</span>
                        </label>
                        <label class="check">
                            <input type="checkbox" name="check_criticas" value="1" required @checked(old('check_criticas')) @disabled(! $puede_cerrar)>
                            <span>Informé cajas críticas en proceso (ej. autoclave)</span>
                        </label>
                        <label class="check">
                            <input type="checkbox" name="check_stock" value="1" @checked(old('check_stock')) @disabled(! $puede_cerrar)>
                            <span>Stock bajo comunicado a administradora <span class="cierre-opcional">(si aplica)</span></span>
                        </label>
                    </div>
                    @error('check_alertas')
                        <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                    @enderror
                    @error('check_criticas')
                        <p class="entrega-error-campo" role="alert">{{ $message }}</p>
                    @enderror
                    <p class="entrega-error-campo" id="err-checks" hidden role="alert">Marca los ítems obligatorios del checklist.</p>
                </fieldset>

                <div class="recv-actions cierre-acciones">
                    <a class="btn btn-ghost" href="{{ route('mockups.panel', 'enfermera') }}">Cancelar</a>
                    <button class="btn btn-primary cierre-confirmar" type="submit" @disabled(! $puede_cerrar)>
                        Confirmar cierre de turno
                    </button>
                </div>
            </form>

            <aside class="cierre-aside">
                <section class="panel cierre-resumen">
                    <div class="panel-head"><h2>Resumen del turno</h2></div>
                    <div class="kpi-row cierre-kpis">
                        @foreach ($resumen as $k)
                            <div class="kpi {{ ($k['alerta'] ?? false) ? 'kpi-alerta' : '' }}">
                                <div class="l">{{ $k['label'] }}</div>
                                <div class="n">{{ $k['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    <p class="recv-help cierre-ops">Operadoras del bloque: <strong>{{ $turno['operadoras'] }}</strong></p>
                </section>

                <section class="panel list">
                    <div class="panel-head">
                        <h2>Historial reciente</h2>
                        <span class="badge">Bitácora</span>
                    </div>
                    @if (count($historial) === 0)
                        <p class="cierre-vacio" style="padding:0.5rem 0 0;">Aún no hay cierres registrados.</p>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Bloque</th>
                                        <th>Responsable</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historial as $h)
                                        <tr>
                                            <td>{{ $h['fecha'] }}</td>
                                            <td>{{ $h['bloque'] }}</td>
                                            <td><strong>{{ $h['responsable'] }}</strong></td>
                                            <td><span class="pill pill-ok">{{ $h['estado'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    <div class="panel-note">Bitácora de responsabilidad del turno. No reemplaza los reportes de administradora.</div>
                </section>
            </aside>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
(() => {
    document.querySelectorAll('[data-cierre-pager]').forEach((bloque) => {
        const items = [...bloque.querySelectorAll('[data-cierre-item]')];
        const pager = bloque.querySelector('.cierre-pager');
        const meta = bloque.querySelector('.cierre-pager-meta');
        const prev = bloque.querySelector('[data-cierre-prev]');
        const next = bloque.querySelector('[data-cierre-next]');
        const size = Math.max(1, Number(bloque.dataset.pageSize || 5));
        if (!pager || items.length <= size) return;

        let page = 1;
        const pages = Math.ceil(items.length / size);

        const render = () => {
            const from = (page - 1) * size;
            const to = from + size;
            items.forEach((el, i) => {
                el.hidden = i < from || i >= to;
            });
            if (meta) {
                meta.textContent = `${from + 1}–${Math.min(to, items.length)} de ${items.length}`;
            }
            pager.hidden = false;
            if (prev) prev.disabled = page <= 1;
            if (next) next.disabled = page >= pages;
        };

        prev?.addEventListener('click', () => {
            if (page > 1) {
                page -= 1;
                render();
            }
        });
        next?.addEventListener('click', () => {
            if (page < pages) {
                page += 1;
                render();
            }
        });
        render();
    });

    const form = document.getElementById('form-cierre-turno');
    const relevo = document.getElementById('relevo');
    const wrapRelevo = document.getElementById('wrap-relevo');
    const wrapChecks = document.getElementById('wrap-checks');
    const vista = document.getElementById('relevo-vista');
    const errRelevo = document.getElementById('err-relevo');
    const errChecks = document.getElementById('err-checks');
    if (!form || !relevo || relevo.disabled) return;

    const marcar = (wrap, err, invalido) => {
        wrap?.classList.toggle('is-invalido', invalido);
        if (err) err.hidden = !invalido;
    };

    relevo.addEventListener('change', () => {
        const opt = relevo.options[relevo.selectedIndex];
        if (vista && opt) vista.textContent = opt.textContent.trim();
        marcar(wrapRelevo, errRelevo, false);
    });

    form.querySelectorAll('input[type="checkbox"][required]').forEach((c) => {
        c.addEventListener('change', () => marcar(wrapChecks, errChecks, false));
    });

    form.addEventListener('submit', (e) => {
        const relevoOk = Boolean(relevo.value);
        const checksOk = [...form.querySelectorAll('input[type="checkbox"][required]')].every((c) => c.checked);

        marcar(wrapRelevo, errRelevo, !relevoOk);
        marcar(wrapChecks, errChecks, !checksOk);

        if (!relevoOk || !checksOk) {
            e.preventDefault();
            form.querySelector('.entrega-error-campo:not([hidden])')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
})();
</script>
@endpush
