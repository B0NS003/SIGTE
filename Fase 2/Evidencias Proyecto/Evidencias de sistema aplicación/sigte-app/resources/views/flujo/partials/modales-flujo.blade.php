<div
    id="flujo-modales"
    data-accion="{{ $accion_modal }}"
    data-caja="{{ $caja_modal }}"
    data-listo="{{ session('ok') && $accion_modal === 'etapa' ? '1' : '0' }}"
    data-corregido="{{ session('ok') && $accion_modal === 'retroceso' ? '1' : '0' }}"
    data-sin-puesto="{{ $puesto ? '0' : '1' }}"
    data-anotar="{{ route('mockups.anotar') }}"
    data-entrega="{{ url('/operador/entrega') }}"
>
    <dialog id="modal-etapa" class="flujo-modal" aria-labelledby="etapa-titulo">
        <div class="flujo-modal-cuerpo">
            <div class="flujo-modal-top">
                <h2 id="etapa-titulo"></h2>
            </div>
            <p class="flujo-ruta" id="etapa-ruta">
                <span class="flujo-ruta-desde" id="etapa-ruta-desde"></span>
                <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="flujo-ruta-hacia" id="etapa-ruta-hacia"></span>
            </p>

            @unless ($puesto)
                <div class="flujo-sin-puesto" role="alert">
                    <p>Antes de anotar o pasar la caja, elige quién está en el puesto.</p>
                    <button class="btn btn-ghost" type="button" data-elegir-puesto>Elegir operadora</button>
                </div>
            @endunless

            <p class="auth-error" id="etapa-error" role="alert" @unless($errors->any() && $accion_modal === 'etapa') hidden @endunless>{{ $errors->first() }}</p>
            <p class="recv-help" id="etapa-vacio" hidden>Esa caja no está en el flujo.</p>

            <form id="etapa-pedir" action="{{ route('mockups.avanzar.guardar') }}" method="post">
                @csrf
                <input type="hidden" name="caja" id="etapa-caja-input" value="{{ $caja_modal }}">
                <input type="hidden" name="etapa_destino" id="etapa-destino" value="">
                @if ($busqueda !== '')
                    <input type="hidden" name="q" value="{{ $busqueda }}">
                @endif
                @if ($etapa_filtro !== null)
                    <input type="hidden" name="etapa" value="{{ $etapa_filtro }}">
                @endif

                <section class="flujo-paso" id="etapa-anotar" aria-labelledby="etapa-anotar-titulo">
                    <div class="flujo-paso-cabeza">
                        <p class="flujo-paso-titulo" id="etapa-anotar-titulo">Anotar</p>
                        <span class="flujo-opcional">Opcional</span>
                    </div>
                    <p class="flujo-paso-resumen" id="etapa-anotar-ayuda"></p>
                    <div class="flujo-chips" id="etapa-chips"></div>

                    <div id="etapa-detalle-bloque" hidden>
                        <label class="field-label" id="etapa-detalle-label" for="etapa-detalle"></label>
                        <div class="flujo-tiempo">
                            <input class="flujo-detalle" id="etapa-detalle" maxlength="120" autocomplete="off">
                            <button class="flujo-chip" id="etapa-detalle-guardar" type="button">Anotar</button>
                        </div>
                    </div>

                    <div id="etapa-tiempos"></div>
                    <p class="auth-error" id="etapa-anota-error" role="alert" hidden></p>
                </section>

                <section class="flujo-pasar" id="etapa-pasar" aria-labelledby="etapa-pasar-titulo">
                    <p class="flujo-paso-titulo" id="etapa-pasar-titulo">Pasar de etapa</p>
                    @if ($puesto)
                        <p class="flujo-paso-resumen">Queda registrado a nombre de {{ $puesto->name }}, con fecha y hora.</p>
                    @endif
                    <p class="flujo-pendiente" id="etapa-pendiente" hidden></p>
                    <div class="flujo-modal-acciones">
                        <button class="btn btn-ghost" type="button" data-cerrar>Cerrar</button>
                        <button class="btn btn-primary" type="submit" id="etapa-enviar">
                            <span id="etapa-enviar-texto">Pasar de etapa</span>
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9.5 6.5 15 12l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <a class="btn btn-primary" id="etapa-entrega" href="{{ route('mockups.entrega') }}" hidden>
                            <span>Registrar la entrega</span>
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9.5 6.5 15 12l-5.5 5.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </section>
            </form>

            <details class="flujo-historial" id="etapa-historial" hidden>
                <summary>Historial de la caja <span id="etapa-historial-total"></span></summary>
                <ol class="act-lista" id="etapa-lista"></ol>
            </details>
        </div>
    </dialog>

    <dialog id="modal-retroceso" class="flujo-modal" aria-labelledby="retro-titulo">
        <div class="flujo-modal-cuerpo">
            <div class="flujo-modal-top">
                <h2 id="retro-titulo"></h2>
            </div>

            @unless ($puesto)
                <div class="flujo-sin-puesto" role="alert">
                    <p>Antes de corregir la caja, elige quién está en el puesto.</p>
                    <button class="btn btn-ghost" type="button" data-elegir-puesto>Elegir operadora</button>
                </div>
            @endunless

            <p class="auth-error" id="retro-error" role="alert" @unless($errors->any() && $accion_modal === 'retroceso') hidden @endunless>{{ $accion_modal === 'retroceso' ? $errors->first() : '' }}</p>

            <form id="retro-pedir" action="{{ route('mockups.retroceder') }}" method="post">
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
                    <p class="flujo-paso-resumen">@if ($puesto) Lo registra {{ $puesto->name }}. @endif Esta corrección queda con la fecha y la hora.</p>
                    <label class="field-label" for="retro-motivo">Motivo</label>
                    <textarea class="flujo-motivo" id="retro-motivo" name="motivo" maxlength="180" required placeholder="Qué pasó con esta caja">{{ $accion_modal === 'retroceso' ? old('motivo') : '' }}</textarea>
                    <div class="flujo-modal-acciones">
                        <button class="btn btn-ghost" type="button" data-cerrar>Cancelar</button>
                        <button class="btn btn-primary" type="submit" id="retro-enviar">
                            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span id="retro-enviar-texto">Confirmar corrección</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </dialog>

    <div class="flujo-aviso" id="flujo-aviso" role="status" hidden>
        <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M5.5 12.5 10 17l8.5-9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p id="flujo-aviso-texto">{{ session('ok') }}</p>
        <button type="button" class="flujo-aviso-cerrar" aria-label="Cerrar aviso">
            <svg class="sigte-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M7 7l10 10M17 7 7 17" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
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
                var sinPuesto = raiz.dataset.sinPuesto === '1';
                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                var formEtapa = document.getElementById('etapa-pedir');
                var enviar = document.getElementById('etapa-enviar');
                var enviarTexto = document.getElementById('etapa-enviar-texto');
                var cardActual = null;

                function tarjetas() {
                    return Array.prototype.slice.call(document.querySelectorAll('.track-card[data-caja]'));
                }

                function tarjeta(id) {
                    return id ? document.querySelector('.track-card[data-caja="' + CSS.escape(id) + '"]') : null;
                }

                function primera() {
                    var cards = tarjetas();
                    return cards.length ? cards[0].dataset.caja : '';
                }

                function limpiarUrl() {
                    var url = new URL(window.location.href);
                    if (!url.searchParams.has('accion')) {
                        return;
                    }
                    url.searchParams.delete('accion');
                    url.searchParams.delete('caja');
                    window.history.replaceState({}, '', url);
                }

                function hora(nota) {
                    var partes = String(nota.cuando || '').split(' · ');
                    return partes[1] || partes[0] || '';
                }

                // Las notas llegan en orden; la etapa actual empieza después del último cambio de etapa.
                function notasDeEtapa(id) {
                    var filas = notas[id] || [];
                    var desde = 0;
                    filas.forEach(function (nota, indice) {
                        if (/^(Pasó a |Volvió a |Recepción\.)/.test(nota.texto)) {
                            desde = indice + 1;
                        }
                    });
                    return filas.slice(desde);
                }

                function ultimoTiempo(id, etiqueta) {
                    var hallado = null;
                    notasDeEtapa(id).forEach(function (nota) {
                        if (nota.texto.indexOf(etiqueta + ': ') === 0) {
                            hallado = nota;
                        }
                    });
                    return hallado;
                }

                function opcionHecha(id, nombre) {
                    return notasDeEtapa(id).some(function (nota) {
                        return nota.texto === nombre || nota.texto.indexOf(nombre + '. ') === 0;
                    });
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
                    document.getElementById('etapa-historial-total').textContent = filas.length ? '(' + filas.length + ')' : '';
                    filas.slice().reverse().forEach(function (nota) {
                        var meta = nota.cuando;
                        if (nota.quien) {
                            meta += ' · ' + nota.quien;
                        }
                        lista.appendChild(fila(nota.texto, meta));
                    });
                }

                function mostrarError(texto) {
                    var aviso = document.getElementById('etapa-anota-error');
                    aviso.hidden = !texto;
                    aviso.textContent = texto || '';
                }

                function chip(nombre, hecho, alClick) {
                    var boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'flujo-chip' + (hecho ? ' is-hecho' : '');
                    boton.textContent = nombre;
                    if (hecho) {
                        boton.setAttribute('aria-label', nombre + ', ya anotado. Anotar otra vez');
                    }
                    boton.addEventListener('click', alClick);
                    return boton;
                }

                function filaTiempo(id, tiempo) {
                    var hecho = ultimoTiempo(id, tiempo.etiqueta);
                    var fila = document.createElement('div');
                    var texto = document.createElement('span');
                    var nombre = document.createElement('strong');
                    var estado = document.createElement('small');
                    var input = document.createElement('input');
                    var unidad = document.createElement('span');
                    var boton = document.createElement('button');
                    fila.className = 'flujo-tiempo' + (hecho ? ' is-hecho' : '');
                    fila.dataset.clave = tiempo.clave;
                    fila.dataset.etiqueta = tiempo.etiqueta;
                    texto.className = 'flujo-tiempo-texto';
                    nombre.textContent = tiempo.etiqueta;
                    estado.textContent = hecho
                        ? 'Anotado: ' + hecho.texto.slice(tiempo.etiqueta.length + 2) + ' · ' + hora(hecho)
                        : 'Sin anotar';
                    texto.append(nombre, estado);
                    input.type = 'number';
                    input.min = '1';
                    input.max = '1440';
                    input.inputMode = 'numeric';
                    input.className = 'flujo-minutos';
                    input.setAttribute('aria-label', 'Minutos de ' + tiempo.etiqueta);
                    input.placeholder = tiempo.sugerido ? String(tiempo.sugerido) : '0';
                    unidad.textContent = 'min';
                    boton.type = 'button';
                    boton.className = 'flujo-chip';
                    boton.textContent = 'Anotar';
                    boton.addEventListener('click', function () {
                        guardarTiempo(id, fila).then(function (ok) {
                            if (ok) {
                                pintarAnotar(tarjeta(id), '#etapa-tiempos [data-clave="' + tiempo.clave + '"] button');
                            }
                        });
                    });
                    input.addEventListener('input', actualizarPendiente);
                    input.addEventListener('keydown', function (evento) {
                        if (evento.key === 'Enter') {
                            evento.preventDefault();
                            boton.click();
                        }
                    });
                    fila.append(texto, input, unidad, boton);
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
                    mostrarError('');
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

                function pintarAnotar(card, foco) {
                    if (!card) {
                        return;
                    }
                    var id = card.dataset.caja;
                    var paso = pasos[card.dataset.etapa] || {};
                    var complementos = paso.complementos || {};
                    var opciones = paso.opciones || [];
                    var filasTiempo = paso.tiempos || [];
                    ocultarDetalle();
                    document.getElementById('etapa-anotar-titulo').textContent = 'Anotar en ' + card.dataset.fase;
                    document.getElementById('etapa-anotar-ayuda').textContent = paso.ayuda || '';
                    var caja = document.getElementById('etapa-chips');
                    caja.innerHTML = '';
                    opciones.forEach(function (nombre, indice) {
                        caja.appendChild(chip(nombre, opcionHecha(id, nombre), function (evento) {
                            if (complementos[nombre]) {
                                mostrarDetalle(id, nombre, complementos[nombre], evento.currentTarget);
                                return;
                            }
                            ocultarDetalle();
                            guardarNota(id, { texto: nombre }).then(function (ok) {
                                if (ok) {
                                    pintarAnotar(tarjeta(id), '#etapa-chips .flujo-chip:nth-child(' + (indice + 1) + ')');
                                }
                            });
                        }));
                    });
                    caja.hidden = !opciones.length;
                    var tiempos = document.getElementById('etapa-tiempos');
                    tiempos.innerHTML = '';
                    tiempos.hidden = !filasTiempo.length;
                    filasTiempo.forEach(function (tiempo) {
                        tiempos.appendChild(filaTiempo(id, tiempo));
                    });
                    var soloConfirmar = !opciones.length && !filasTiempo.length;
                    document.getElementById('etapa-anotar').hidden = soloConfirmar;
                    document.getElementById('etapa-pasar').classList.toggle('is-solo', soloConfirmar);
                    pintarLista(id);
                    bloquearSinPuesto();
                    actualizarPendiente();
                    var enfocar = foco ? etapa.querySelector(foco) : null;
                    if (enfocar) {
                        enfocar.focus();
                    }
                }

                function filasPendientes() {
                    return Array.prototype.filter.call(document.querySelectorAll('#etapa-tiempos .flujo-tiempo'), function (filaTiempo) {
                        return filaTiempo.querySelector('.flujo-minutos').value.trim() !== '';
                    });
                }

                function actualizarPendiente() {
                    if (!cardActual || !cardActual.dataset.destino) {
                        return;
                    }
                    var pendientes = filasPendientes();
                    var aviso = document.getElementById('etapa-pendiente');
                    var destino = 'Pasar a ' + cardActual.dataset.destinoNombre;
                    if (!pendientes.length) {
                        aviso.hidden = true;
                        enviarTexto.textContent = destino;
                        return;
                    }
                    var lista = pendientes.map(function (filaTiempo) {
                        return filaTiempo.dataset.etiqueta + ' (' + filaTiempo.querySelector('.flujo-minutos').value + ' min)';
                    }).join(' y ');
                    aviso.textContent = lista + (pendientes.length > 1 ? ' todavía no están anotados.' : ' todavía no está anotado.') + ' Se anota al pasar; si no corresponde, borra el número.';
                    aviso.hidden = false;
                    enviarTexto.textContent = 'Anotar y ' + destino.charAt(0).toLowerCase() + destino.slice(1);
                }

                function bloquearSinPuesto() {
                    if (!sinPuesto) {
                        return;
                    }
                    etapa.querySelectorAll('#etapa-pedir button:not([data-cerrar]), #etapa-pedir input:not([type="hidden"])').forEach(function (control) {
                        control.disabled = true;
                    });
                    document.getElementById('etapa-entrega').setAttribute('aria-disabled', 'true');
                }

                function pintarEtapa(id) {
                    var card = tarjeta(id);
                    var vacio = document.getElementById('etapa-vacio');
                    cardActual = card;
                    if (!card) {
                        vacio.hidden = false;
                        formEtapa.hidden = true;
                        pintarLista('');
                        return;
                    }
                    vacio.hidden = true;
                    formEtapa.hidden = false;
                    document.getElementById('etapa-caja-input').value = card.dataset.caja;
                    document.getElementById('etapa-destino').value = card.dataset.destino || '';
                    document.getElementById('etapa-titulo').textContent = card.dataset.caja + ' · ' + card.dataset.servicio;
                    document.getElementById('etapa-ruta-desde').textContent = card.dataset.fase;
                    document.getElementById('etapa-ruta-hacia').textContent = card.dataset.destinoNombre || 'Entrega al servicio';
                    var entrega = document.getElementById('etapa-entrega');
                    var tituloPasar = document.getElementById('etapa-pasar-titulo');
                    if (card.dataset.destino) {
                        tituloPasar.textContent = 'Pasar a ' + card.dataset.destinoNombre;
                        enviar.hidden = false;
                        entrega.hidden = true;
                    } else {
                        tituloPasar.textContent = 'Registrar entrega';
                        enviar.hidden = true;
                        entrega.hidden = false;
                        entrega.href = raiz.dataset.entrega + '?caja=' + encodeURIComponent(card.dataset.caja);
                    }
                    enviar.disabled = false;
                    delete formEtapa.dataset.anotado;
                    pintarAnotar(card);
                }

                function guardarTiempo(id, filaTiempo) {
                    var minutos = parseInt(filaTiempo.querySelector('.flujo-minutos').value, 10);
                    if (!minutos || minutos < 1 || minutos > 1440) {
                        mostrarError('Indica los minutos de ' + filaTiempo.dataset.etiqueta + ' (entre 1 y 1440).');
                        return Promise.resolve(false);
                    }
                    return guardarNota(id, { clave: filaTiempo.dataset.clave, minutos: minutos });
                }

                function guardarNota(id, datos) {
                    mostrarError('');
                    datos.caja = id;
                    return fetch(raiz.dataset.anotar, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(datos)
                    }).then(function (respuesta) {
                        return respuesta.json().then(function (cuerpo) {
                            if (!respuesta.ok) {
                                throw new Error(
                                    cuerpo.mensaje
                                    || cuerpo.message
                                    || (respuesta.status === 419
                                        ? 'La sesión expiró. Recarga la página e intenta de nuevo.'
                                        : 'No se pudo anotar.')
                                );
                            }
                            return cuerpo;
                        }).catch(function (error) {
                            if (error instanceof Error && error.message && error.message !== 'Unexpected end of JSON input') {
                                throw error;
                            }
                            if (respuesta.status === 419) {
                                throw new Error('La sesión expiró. Recarga la página e intenta de nuevo.');
                            }
                            if (respuesta.redirected || respuesta.status === 302 || respuesta.status === 403) {
                                throw new Error('No tienes permiso para anotar con esta cuenta. Entra como operadora.');
                            }
                            throw new Error('No se pudo anotar.');
                        });
                    }).then(function (nota) {
                        notas[id] = notas[id] || [];
                        notas[id].push(nota);
                        var cardNota = tarjeta(id);
                        var hecho = cardNota ? cardNota.querySelector('[data-actividad]') : null;
                        if (hecho && nota.actividad) {
                            hecho.textContent = nota.actividad;
                        }
                        if (nota.proceso_hasta && window.sigteProceso) {
                            window.sigteProceso.marcar(id, nota.proceso_desde, nota.proceso_hasta);
                        }
                        return true;
                    }).catch(function (error) {
                        mostrarError(error.message);
                        return false;
                    });
                }

                function abrirEtapa(id, modo, conservarError) {
                    var elegido = tarjeta(id) ? id : primera();
                    if (!conservarError) {
                        document.getElementById('etapa-error').hidden = true;
                    }
                    mostrarError('');
                    pintarEtapa(elegido);
                    var historial = document.getElementById('etapa-historial');
                    historial.open = false;
                    if (!etapa.open) {
                        etapa.showModal();
                    }
                    var foco = modo === 'anotar'
                        ? etapa.querySelector('#etapa-chips .flujo-chip:not(:disabled), #etapa-tiempos .flujo-minutos:not(:disabled)')
                        : (enviar.hidden ? document.getElementById('etapa-entrega') : enviar);
                    if (foco && !foco.disabled) {
                        foco.focus();
                    }
                }

                formEtapa.addEventListener('submit', function (evento) {
                    var pendientes = filasPendientes();
                    if (formEtapa.dataset.anotado === '1' || !pendientes.length || !cardActual) {
                        enviar.disabled = true;
                        return;
                    }
                    evento.preventDefault();
                    enviar.disabled = true;
                    var id = cardActual.dataset.caja;
                    pendientes.reduce(function (cadena, filaTiempo) {
                        return cadena.then(function (ok) {
                            return ok ? guardarTiempo(id, filaTiempo) : false;
                        });
                    }, Promise.resolve(true)).then(function (ok) {
                        if (!ok) {
                            enviar.disabled = false;
                            pintarAnotar(cardActual);
                            return;
                        }
                        formEtapa.dataset.anotado = '1';
                        formEtapa.submit();
                    });
                });

                document.getElementById('etapa-detalle-guardar').addEventListener('click', function () {
                    var bloque = document.getElementById('etapa-detalle-bloque');
                    var valor = document.getElementById('etapa-detalle').value.trim();
                    if (!valor) {
                        mostrarError(bloque.dataset.falta || 'Falta el detalle.');
                        return;
                    }
                    var texto = bloque.dataset.texto;
                    guardarNota(bloque.dataset.caja, { texto: texto, detalle: valor }).then(function (ok) {
                        if (!ok) {
                            return;
                        }
                        pintarAnotar(tarjeta(bloque.dataset.caja));
                        var chipHecho = Array.prototype.find.call(etapa.querySelectorAll('#etapa-chips .flujo-chip'), function (boton) {
                            return boton.textContent === texto;
                        });
                        if (chipHecho) {
                            chipHecho.focus();
                        }
                    });
                });
                document.getElementById('etapa-detalle').addEventListener('keydown', function (evento) {
                    if (evento.key === 'Enter') {
                        evento.preventDefault();
                        document.getElementById('etapa-detalle-guardar').click();
                    }
                });

                document.querySelectorAll('[data-abrir="etapa"]').forEach(function (boton) {
                    boton.addEventListener('click', function () {
                        abrirEtapa(boton.dataset.caja, boton.dataset.modo || 'pasar', false);
                    });
                });

                function prepararDialogo(dialogo) {
                    dialogo.addEventListener('click', function (evento) {
                        if (evento.target === dialogo) {
                            dialogo.close();
                        }
                    });
                    dialogo.querySelectorAll('[data-cerrar]').forEach(function (boton) {
                        boton.addEventListener('click', function () {
                            dialogo.close();
                        });
                    });
                    dialogo.addEventListener('close', limpiarUrl);
                }

                prepararDialogo(etapa);

                document.querySelectorAll('[data-elegir-puesto]').forEach(function (boton) {
                    boton.addEventListener('click', function () {
                        var abierto = boton.closest('dialog');
                        if (abierto) {
                            abierto.close();
                        }
                        var puesto = document.querySelector('.puesto-card');
                        if (!puesto) {
                            return;
                        }
                        puesto.scrollIntoView({ block: 'center', behavior: reduce ? 'auto' : 'smooth' });
                        var lista = puesto.querySelector('.sigte-lista-boton') || puesto.querySelector('select');
                        if (!lista) {
                            return;
                        }
                        // Fuera del clic actual: el cierre de listas escucha los clics en document.
                        window.setTimeout(function () {
                            lista.focus();
                            if (lista.classList.contains('sigte-lista-boton')) {
                                lista.click();
                            }
                        }, reduce ? 0 : 350);
                    });
                });

                var aviso = document.getElementById('flujo-aviso');
                var avisoTimer = null;

                function ocultarAviso() {
                    aviso.hidden = true;
                    window.clearTimeout(avisoTimer);
                }

                function avisar(codigo) {
                    if (!document.getElementById('flujo-aviso-texto').textContent.trim()) {
                        return;
                    }
                    aviso.hidden = false;
                    limpiarUrl();
                    avisoTimer = window.setTimeout(ocultarAviso, 6000);
                    var card = tarjeta(codigo);
                    if (!card) {
                        return;
                    }
                    var grupo = card.closest('details');
                    if (grupo) {
                        grupo.open = true;
                    }
                    card.scrollIntoView({ block: 'center', behavior: reduce ? 'auto' : 'smooth' });
                    card.classList.add('is-recien');
                    window.setTimeout(function () {
                        card.classList.remove('is-recien');
                    }, 2600);
                }

                aviso.querySelector('.flujo-aviso-cerrar').addEventListener('click', ocultarAviso);

                if (raiz.dataset.listo === '1' || raiz.dataset.corregido === '1') {
                    avisar(raiz.dataset.caja);
                } else if (raiz.dataset.accion === 'etapa') {
                    abrirEtapa(raiz.dataset.caja, 'pasar', true);
                }

                var retro = document.getElementById('modal-retroceso');
                if (!retro) {
                    return;
                }

                function abrirRetroceso(id, conservarError) {
                    var card = tarjeta(id);
                    var boton = document.querySelector('[data-abrir="retroceso"][data-caja="' + CSS.escape(id) + '"]');
                    document.getElementById('retro-titulo').textContent = card
                        ? card.dataset.caja + ' · ' + card.dataset.servicio
                        : id;
                    var volver = boton ? boton.dataset.volver : '';
                    document.getElementById('retro-movimiento').textContent = volver ? 'Vuelve a ' + volver : 'Corregir etapa';
                    document.getElementById('retro-enviar-texto').textContent = volver ? 'Volver a ' + volver : 'Confirmar corrección';
                    document.getElementById('retro-caja').value = id;
                    if (!conservarError) {
                        var error = document.getElementById('retro-error');
                        error.hidden = true;
                        error.textContent = '';
                        document.getElementById('retro-motivo').value = '';
                    }
                    if (sinPuesto) {
                        retro.querySelectorAll('#retro-pedir button:not([data-cerrar]), #retro-pedir textarea').forEach(function (control) {
                            control.disabled = true;
                        });
                    }
                    if (!retro.open) {
                        retro.showModal();
                    }
                }

                document.querySelectorAll('[data-abrir="retroceso"]').forEach(function (boton) {
                    boton.addEventListener('click', function () {
                        abrirRetroceso(boton.dataset.caja, false);
                    });
                });
                prepararDialogo(retro);

                if (raiz.dataset.accion === 'retroceso' && raiz.dataset.corregido !== '1') {
                    abrirRetroceso(raiz.dataset.caja, true);
                }
            })();
        </script>
    @endpush
@endonce
