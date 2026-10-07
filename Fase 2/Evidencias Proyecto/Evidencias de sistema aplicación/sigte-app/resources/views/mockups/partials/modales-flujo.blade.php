<div
    id="flujo-modales"
    data-accion="{{ $accion_modal }}"
    data-caja="{{ $caja_modal }}"
    data-listo="{{ session('ok') && $accion_modal === 'etapa' ? '1' : '0' }}"
    data-corregido="{{ session('ok') && $accion_modal === 'retroceso' ? '1' : '0' }}"
    data-anotar="{{ route('mockups.anotar') }}"
    data-entrega="{{ url('/operador/entrega') }}"
>
    <dialog id="modal-etapa" class="flujo-modal" aria-labelledby="etapa-titulo">
        <div class="flujo-modal-cuerpo">
            <div class="flujo-modal-top">
                <h2 id="etapa-titulo"></h2>
            </div>

            <p class="auth-error" id="etapa-error" role="alert" @unless($errors->any() && $accion_modal === 'etapa') hidden @endunless>{{ $errors->first() }}</p>
            <p class="recv-help" id="etapa-vacio" hidden>Esa caja no está en el flujo.</p>

            <div id="etapa-listo" @unless(session('ok') && $accion_modal === 'etapa') hidden @endunless>
                <p class="eyebrow">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Listo
                </p>
                <p class="adv-movimiento" id="etapa-listo-titulo">{{ session('ok') }}</p>
                <p class="recv-help" id="etapa-listo-meta"></p>
            </div>

            <form id="etapa-pedir" action="{{ route('mockups.avanzar.guardar') }}" method="post" @if(session('ok') && $accion_modal === 'etapa') hidden @endif>
                @csrf
                <input type="hidden" name="caja" id="etapa-caja-input" value="{{ $caja_modal }}">
                <input type="hidden" name="etapa_destino" id="etapa-destino" value="">
                @if ($busqueda !== '')
                    <input type="hidden" name="q" value="{{ $busqueda }}">
                @endif
                @if ($etapa_filtro !== null)
                    <input type="hidden" name="etapa" value="{{ $etapa_filtro }}">
                @endif

                <div class="flujo-paso" id="etapa-anotar">
                    <p class="flujo-paso-titulo">En esta etapa</p>
                    <div class="flujo-chips" id="etapa-chips"></div>

                    <div id="etapa-detalle-bloque" hidden>
                        <label class="field-label" id="etapa-detalle-label" for="etapa-detalle"></label>
                        <div class="flujo-tiempo">
                            <input class="flujo-detalle" id="etapa-detalle" maxlength="120" autocomplete="off">
                            <button class="flujo-chip" id="etapa-detalle-guardar" type="button">Registrar</button>
                        </div>
                    </div>

                    <div id="etapa-tiempo-bloque" hidden>
                        <p class="field-label">Tiempo</p>
                        <div id="etapa-tiempos"></div>
                    </div>
                    <p class="auth-error" id="etapa-anota-error" role="alert" hidden></p>
                </div>

                <div class="flujo-pasar" id="etapa-pasar">
                    <p class="flujo-paso-titulo" id="etapa-pasar-titulo">Pasar de etapa</p>
                    <div class="flujo-modal-acciones">
                        <button class="btn btn-ghost" type="button" data-cerrar>
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M7 7l10 10M17 7 7 17" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            </svg>
                            Cancelar
                        </button>
                        <button class="btn btn-primary" type="submit" id="etapa-enviar">
                            <span id="etapa-enviar-texto">Pasar de etapa</span>
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9.5 6.5 15 12l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <a class="btn btn-primary" id="etapa-entrega" href="{{ route('mockups.entrega') }}" hidden>
                            <span id="etapa-entrega-texto">Registrar la entrega</span>
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9.5 6.5 15 12l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </form>

            <section class="act-suma" id="etapa-historial" hidden>
                <h2>En esta caja</h2>
                <ol class="act-lista" id="etapa-lista"></ol>
            </section>

            <div class="flujo-modal-acciones" id="etapa-listo-cerrar" @unless(session('ok') && $accion_modal === 'etapa') hidden @endunless>
                <button class="btn btn-ghost" type="button" data-cerrar>
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14.5 6.5 9 12l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Volver al flujo
                </button>
            </div>
        </div>
    </dialog>

    <dialog id="modal-retroceso" class="flujo-modal" aria-labelledby="retro-titulo">
        <div class="flujo-modal-cuerpo">
            <div class="flujo-modal-top">
                <h2 id="retro-titulo"></h2>
            </div>

            <p class="auth-error" id="retro-error" role="alert" @unless($errors->any() && $accion_modal === 'retroceso') hidden @endunless>{{ $accion_modal === 'retroceso' ? $errors->first() : '' }}</p>

            <div id="retro-listo" @unless(session('ok') && $accion_modal === 'retroceso') hidden @endunless>
                <p class="eyebrow">
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Corregido
                </p>
                <p class="adv-movimiento" id="retro-listo-titulo">{{ $accion_modal === 'retroceso' ? session('ok') : '' }}</p>
                <p class="recv-help" id="retro-listo-meta"></p>
            </div>

            <form id="retro-pedir" action="{{ route('mockups.retroceder') }}" method="post" @if(session('ok') && $accion_modal === 'retroceso') hidden @endif>
                @csrf
                <input type="hidden" name="caja" id="retro-caja" value="{{ $accion_modal === 'retroceso' ? $caja_modal : '' }}">
                @if ($busqueda !== '')
                    <input type="hidden" name="q" value="{{ $busqueda }}">
                @endif
                @if ($etapa_filtro !== null)
                    <input type="hidden" name="etapa" value="{{ $etapa_filtro }}">
                @endif

                <div class="flujo-paso">
                    <p class="flujo-paso-titulo" id="retro-movimiento"></p>
                    <p class="flujo-paso-resumen">Esta corrección queda con la fecha, la hora y tu nombre.</p>
                    <label class="field-label" for="retro-motivo">Motivo</label>
                    <textarea class="flujo-motivo" id="retro-motivo" name="motivo" maxlength="180" required placeholder="Qué pasó con esta caja">{{ $accion_modal === 'retroceso' ? old('motivo') : '' }}</textarea>
                    <div class="flujo-modal-acciones">
                        <button class="btn btn-ghost" type="button" data-cerrar>
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M7 7l10 10M17 7 7 17" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            </svg>
                            Cancelar
                        </button>
                        <button class="btn btn-primary" type="submit" id="retro-enviar">
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span id="retro-enviar-texto">Confirmar corrección</span>
                        </button>
                    </div>
                </div>
            </form>

            <div class="flujo-modal-acciones" id="retro-listo-cerrar" @unless(session('ok') && $accion_modal === 'retroceso') hidden @endunless>
                <button class="btn btn-ghost" type="button" data-cerrar>
                    <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14.5 6.5 9 12l5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Volver al flujo
                </button>
            </div>
        </div>
    </dialog>
</div>
<script type="application/json" id="pasos-etapa">@json($pasos_etapa)</script>
<script type="application/json" id="anotaciones-caja">@json($anotaciones_por_caja)</script>
@once
    @push('scripts')
        <script>
            (function () {
                var raiz = document.getElementById('flujo-modales');
                var etapa = document.getElementById('modal-etapa');
                if (!raiz || !etapa) {
                    return;
                }

                var pasos = JSON.parse(document.getElementById('pasos-etapa').textContent || '{}');
                var notas = JSON.parse(document.getElementById('anotaciones-caja').textContent || '{}');
                var token = document.querySelector('meta[name="csrf-token"]').content;

                function tarjetas() {
                    return Array.prototype.slice.call(document.querySelectorAll('.track-card[data-caja]'));
                }

                function tarjeta(id) {
                    return document.querySelector('.track-card[data-caja="' + CSS.escape(id) + '"]');
                }

                function primera() {
                    var cards = tarjetas();
                    return cards.length ? cards[0].dataset.caja : '';
                }

                function fila(titulo, meta) {
                    var li = document.createElement('li');
                    var fuerte = document.createElement('strong');
                    var span = document.createElement('span');
                    fuerte.textContent = titulo;
                    span.textContent = meta;
                    li.append(fuerte, span);
                    return li;
                }

                function pintarLista(id) {
                    var lista = document.getElementById('etapa-lista');
                    var bloque = document.getElementById('etapa-historial');
                    var filas = notas[id] || [];
                    lista.innerHTML = '';
                    bloque.hidden = filas.length === 0;
                    filas.forEach(function (nota) {
                        var meta = nota.cuando;
                        if (nota.quien) {
                            meta += ' · ' + nota.quien;
                        }
                        lista.appendChild(fila(nota.texto, meta));
                    });
                }

                function chip(nombre, alClick) {
                    var boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'flujo-chip';
                    boton.textContent = nombre;
                    boton.addEventListener('click', function (evento) {
                        alClick(evento);
                    });
                    return boton;
                }

                function filaTiempo(id, tiempo) {
                    var fila = document.createElement('div');
                    var nombre = document.createElement('span');
                    var input = document.createElement('input');
                    var unidad = document.createElement('span');
                    var boton = document.createElement('button');
                    fila.className = 'flujo-tiempo';
                    nombre.textContent = tiempo.etiqueta;
                    input.type = 'number';
                    input.min = '1';
                    input.max = '1440';
                    input.inputMode = 'numeric';
                    input.className = 'flujo-minutos';
                    input.setAttribute('aria-label', 'Minutos de ' + tiempo.etiqueta);
                    if (tiempo.sugerido) {
                        input.value = tiempo.sugerido;
                    } else {
                        input.placeholder = '0';
                    }
                    unidad.textContent = 'min';
                    boton.type = 'button';
                    boton.className = 'flujo-chip';
                    boton.textContent = 'Anotar';
                    boton.addEventListener('click', function () {
                        guardarTiempo(id, tiempo.clave, input.value);
                    });
                    input.addEventListener('keydown', function (evento) {
                        if (evento.key === 'Enter') {
                            evento.preventDefault();
                            boton.click();
                        }
                    });
                    fila.append(nombre, input, unidad, boton);
                    return fila;
                }

                function ocultarDetalle() {
                    var bloque = document.getElementById('etapa-detalle-bloque');
                    bloque.hidden = true;
                    document.getElementById('etapa-detalle').value = '';
                    document.querySelectorAll('#etapa-chips .flujo-chip.is-on').forEach(function (boton) {
                        boton.classList.remove('is-on');
                    });
                }

                function mostrarDetalle(id, nombre, complemento, boton) {
                    var bloque = document.getElementById('etapa-detalle-bloque');
                    var aviso = document.getElementById('etapa-anota-error');
                    aviso.hidden = true;
                    aviso.textContent = '';
                    document.querySelectorAll('#etapa-chips .flujo-chip.is-on').forEach(function (otro) {
                        otro.classList.remove('is-on');
                    });
                    boton.classList.add('is-on');
                    bloque.dataset.caja = id;
                    bloque.dataset.texto = nombre;
                    bloque.dataset.falta = complemento.falta;
                    document.getElementById('etapa-detalle-label').textContent = complemento.etiqueta;
                    var campo = document.getElementById('etapa-detalle');
                    campo.placeholder = complemento.placeholder || '';
                    campo.value = '';
                    bloque.hidden = false;
                    campo.focus();
                }

                function pintarChips(card) {
                    var paso = pasos[card.dataset.etapa] || { opciones: [], tiempos: [] };
                    var complementos = paso.complementos || {};
                    var opciones = paso.opciones || [];
                    var filasTiempo = paso.tiempos || [];
                    ocultarDetalle();
                    var caja = document.getElementById('etapa-chips');
                    caja.innerHTML = '';
                    opciones.forEach(function (nombre) {
                        caja.appendChild(chip(nombre, function (evento) {
                            if (complementos[nombre]) {
                                mostrarDetalle(card.dataset.caja, nombre, complementos[nombre], evento.currentTarget);
                                return;
                            }
                            ocultarDetalle();
                            guardarNota(card.dataset.caja, { texto: nombre });
                        }));
                    });
                    caja.hidden = !opciones.length;
                    var bloque = document.getElementById('etapa-tiempo-bloque');
                    var tiempos = document.getElementById('etapa-tiempos');
                    tiempos.innerHTML = '';
                    bloque.hidden = !filasTiempo.length;
                    filasTiempo.forEach(function (tiempo) {
                        tiempos.appendChild(filaTiempo(card.dataset.caja, tiempo));
                    });
                    var anotar = document.getElementById('etapa-anotar');
                    var pasar = document.getElementById('etapa-pasar');
                    var soloConfirmar = !opciones.length && !filasTiempo.length;
                    anotar.hidden = soloConfirmar;
                    pasar.classList.toggle('is-solo', soloConfirmar);
                }

                function pintarEtapa(id) {
                    var card = tarjeta(id);
                    var vacio = document.getElementById('etapa-vacio');
                    var pedir = document.getElementById('etapa-pedir');
                    var listo = document.getElementById('etapa-listo');
                    if (!card) {
                        vacio.hidden = listo.hidden === false;
                        pedir.hidden = true;
                        pintarLista('');
                        return;
                    }
                    vacio.hidden = true;
                    document.getElementById('etapa-caja-input').value = card.dataset.caja;
                    document.getElementById('etapa-destino').value = card.dataset.destino || '';
                    document.getElementById('etapa-titulo').textContent = card.dataset.caja + ' · ' + card.dataset.servicio;
                    var enviar = document.getElementById('etapa-enviar');
                    var entrega = document.getElementById('etapa-entrega');
                    var tituloPasar = document.getElementById('etapa-pasar-titulo');
                    var enviarTexto = document.getElementById('etapa-enviar-texto');
                    if (card.dataset.destino) {
                        tituloPasar.textContent = 'Pasar a ' + card.dataset.destinoNombre;
                        enviar.hidden = false;
                        enviarTexto.textContent = 'Pasar a ' + card.dataset.destinoNombre;
                        entrega.hidden = true;
                    } else {
                        tituloPasar.textContent = 'Registrar entrega';
                        enviar.hidden = true;
                        entrega.hidden = false;
                        entrega.href = raiz.dataset.entrega + '?caja=' + encodeURIComponent(card.dataset.caja);
                    }
                    var meta = document.getElementById('etapa-listo-meta');
                    if (meta && card.dataset.fecha && card.dataset.fecha !== '—') {
                        meta.textContent = card.dataset.fecha + ' · ' + card.dataset.hora + ' · ' + card.dataset.operadora;
                    }
                    pintarChips(card);
                    pintarLista(id);
                }

                function guardarTiempo(id, clave, valor) {
                    var minutos = parseInt(valor, 10);
                    if (!minutos || minutos < 1) {
                        var aviso = document.getElementById('etapa-anota-error');
                        aviso.hidden = false;
                        aviso.textContent = 'Indica los minutos.';
                        return;
                    }
                    guardarNota(id, { clave: clave, minutos: minutos });
                }

                function guardarNota(id, datos) {
                    var aviso = document.getElementById('etapa-anota-error');
                    aviso.hidden = true;
                    datos.caja = id;
                    fetch(raiz.dataset.anotar, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify(datos)
                    }).then(function (respuesta) {
                        return respuesta.json().then(function (cuerpo) {
                            if (!respuesta.ok) {
                                throw new Error(cuerpo.mensaje || 'No se pudo anotar.');
                            }
                            return cuerpo;
                        });
                    }).then(function (nota) {
                        notas[id] = notas[id] || [];
                        notas[id].push(nota);
                        pintarLista(id);
                        ocultarDetalle();
                        var cardNota = tarjeta(id);
                        var hecho = cardNota ? cardNota.querySelector('[data-actividad]') : null;
                        if (hecho && nota.actividad) {
                            hecho.textContent = nota.actividad;
                        }
                        if (nota.proceso_hasta && window.sigteProceso) {
                            window.sigteProceso.marcar(id, nota.proceso_desde, nota.proceso_hasta);
                        }
                    }).catch(function (error) {
                        aviso.hidden = false;
                        aviso.textContent = error.message;
                    });
                }

                function abrirEtapa(id, modoListo, conservarError) {
                    var elegido = tarjeta(id) ? id : primera();
                    document.getElementById('etapa-listo').hidden = !modoListo;
                    document.getElementById('etapa-listo-cerrar').hidden = !modoListo;
                    document.getElementById('etapa-pedir').hidden = !!modoListo;
                    if (!modoListo && !conservarError) {
                        document.getElementById('etapa-error').hidden = true;
                    }
                    var avisoNota = document.getElementById('etapa-anota-error');
                    avisoNota.hidden = true;
                    avisoNota.textContent = '';
                    pintarEtapa(elegido);
                    if (!etapa.open) {
                        etapa.showModal();
                    }
                }

                document.getElementById('etapa-detalle-guardar').addEventListener('click', function () {
                    var bloque = document.getElementById('etapa-detalle-bloque');
                    var valor = document.getElementById('etapa-detalle').value.trim();
                    var aviso = document.getElementById('etapa-anota-error');
                    if (!valor) {
                        aviso.hidden = false;
                        aviso.textContent = bloque.dataset.falta || 'Falta el detalle.';
                        return;
                    }
                    guardarNota(bloque.dataset.caja, { texto: bloque.dataset.texto, detalle: valor });
                });
                document.getElementById('etapa-detalle').addEventListener('keydown', function (evento) {
                    if (evento.key === 'Enter') {
                        evento.preventDefault();
                        document.getElementById('etapa-detalle-guardar').click();
                    }
                });

                document.querySelectorAll('[data-abrir="etapa"]').forEach(function (boton) {
                    boton.addEventListener('click', function () {
                        abrirEtapa(boton.dataset.caja, false, false);
                    });
                });
                etapa.addEventListener('click', function (evento) {
                    if (evento.target === etapa) {
                        etapa.close();
                    }
                });
                etapa.querySelectorAll('[data-cerrar]').forEach(function (boton) {
                    boton.addEventListener('click', function () {
                        etapa.close();
                    });
                });
                etapa.addEventListener('close', function () {
                    var url = new URL(window.location.href);
                    if (!url.searchParams.has('accion')) {
                        return;
                    }
                    url.searchParams.delete('accion');
                    url.searchParams.delete('caja');
                    window.history.replaceState({}, '', url);
                });

                if (raiz.dataset.accion === 'etapa') {
                    abrirEtapa(raiz.dataset.caja, raiz.dataset.listo === '1', true);
                }

                var retro = document.getElementById('modal-retroceso');
                if (!retro) {
                    return;
                }

                function abrirRetroceso(id, corregido, conservarError) {
                    var card = tarjeta(id);
                    var boton = document.querySelector('[data-abrir="retroceso"][data-caja="' + CSS.escape(id) + '"]');
                    document.getElementById('retro-titulo').textContent = card
                        ? card.dataset.caja + ' · ' + card.dataset.servicio
                        : id;
                    var volver = boton ? boton.dataset.volver : '';
                    document.getElementById('retro-movimiento').textContent = volver ? 'Vuelve a ' + volver : 'Corregir etapa';
                    document.getElementById('retro-enviar-texto').textContent = volver ? 'Volver a ' + volver : 'Confirmar corrección';
                    document.getElementById('retro-caja').value = id;
                    document.getElementById('retro-listo').hidden = !corregido;
                    document.getElementById('retro-listo-cerrar').hidden = !corregido;
                    document.getElementById('retro-pedir').hidden = !!corregido;
                    if (!corregido && !conservarError) {
                        var aviso = document.getElementById('retro-error');
                        aviso.hidden = true;
                        aviso.textContent = '';
                        document.getElementById('retro-motivo').value = '';
                    }
                    var meta = document.getElementById('retro-listo-meta');
                    if (meta && card && card.dataset.fecha && card.dataset.fecha !== '—') {
                        meta.textContent = card.dataset.fecha + ' · ' + card.dataset.hora + ' · ' + card.dataset.operadora;
                    }
                    if (!retro.open) {
                        retro.showModal();
                    }
                }

                document.querySelectorAll('[data-abrir="retroceso"]').forEach(function (boton) {
                    boton.addEventListener('click', function () {
                        abrirRetroceso(boton.dataset.caja, false, false);
                    });
                });
                retro.addEventListener('click', function (evento) {
                    if (evento.target === retro) {
                        retro.close();
                    }
                });
                retro.querySelectorAll('[data-cerrar]').forEach(function (boton) {
                    boton.addEventListener('click', function () {
                        retro.close();
                    });
                });
                retro.addEventListener('close', function () {
                    var url = new URL(window.location.href);
                    if (url.searchParams.get('accion') !== 'retroceso') {
                        return;
                    }
                    url.searchParams.delete('accion');
                    url.searchParams.delete('caja');
                    window.history.replaceState({}, '', url);
                });

                if (raiz.dataset.accion === 'retroceso') {
                    abrirRetroceso(raiz.dataset.caja, raiz.dataset.corregido === '1', true);
                }
            })();
        </script>
    @endpush
@endonce
