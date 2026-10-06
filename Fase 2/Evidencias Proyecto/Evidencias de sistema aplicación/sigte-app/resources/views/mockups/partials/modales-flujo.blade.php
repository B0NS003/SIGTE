<div
    id="flujo-modales"
    data-accion="{{ $accion_modal }}"
    data-caja="{{ $caja_modal }}"
    data-listo="{{ session('ok') && $accion_modal === 'etapa' ? '1' : '0' }}"
    data-anotar="{{ route('mockups.anotar') }}"
    data-entrega="{{ url('/operador/entrega') }}"
>
    <dialog id="modal-etapa" class="flujo-modal" aria-labelledby="etapa-titulo">
        <div class="flujo-modal-cuerpo">
            <div class="flujo-modal-top">
                <h2 id="etapa-titulo"></h2>
                <button class="btn btn-ghost" type="button" data-cerrar>Cerrar</button>
            </div>

            <p class="auth-error" id="etapa-error" role="alert" @unless($errors->any() && $accion_modal === 'etapa') hidden @endunless>{{ $errors->first() }}</p>
            <p class="recv-help" id="etapa-vacio" hidden>Esa caja no está en el flujo.</p>

            <div id="etapa-listo" @unless(session('ok') && $accion_modal === 'etapa') hidden @endunless>
                <p class="eyebrow">Listo</p>
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

                <div class="flujo-paso">
                    <p class="flujo-paso-titulo" id="etapa-movimiento"></p>
                    <p class="flujo-paso-resumen" id="etapa-resumen"></p>
                    <div class="flujo-chips" id="etapa-chips"></div>

                    <div id="etapa-tiempo-bloque" hidden>
                        <p class="field-label">Tiempo</p>
                        <div class="flujo-chips" id="etapa-tiempos"></div>
                    </div>
                    <p class="auth-error" id="etapa-anota-error" role="alert" hidden></p>

                    <div class="flujo-modal-acciones">
                        <button class="btn btn-primary" type="submit" id="etapa-enviar">Pasar de etapa</button>
                        <a class="btn btn-primary" id="etapa-entrega" href="{{ route('mockups.entrega') }}" hidden>Registrar la entrega</a>
                        <button class="btn btn-ghost" type="button" data-cerrar>Cancelar</button>
                    </div>
                </div>
            </form>

            <section class="act-suma" id="etapa-historial" hidden>
                <h2>En esta caja</h2>
                <ol class="act-lista" id="etapa-lista"></ol>
            </section>

            <div class="flujo-modal-acciones" id="etapa-listo-cerrar" @unless(session('ok') && $accion_modal === 'etapa') hidden @endunless>
                <button class="btn btn-ghost" type="button" data-cerrar>Volver al flujo</button>
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

                function texto(nodo, partes) {
                    nodo.textContent = '';
                    partes.forEach(function (parte) {
                        if (typeof parte === 'string') {
                            nodo.append(parte);
                            return;
                        }
                        var fuerte = document.createElement('strong');
                        fuerte.textContent = parte.strong;
                        nodo.append(fuerte);
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
                    boton.addEventListener('click', alClick);
                    return boton;
                }

                function pintarChips(card) {
                    var paso = pasos[card.dataset.etapa] || { opciones: [], tiempos: [], tramo: '', resumen: '' };
                    document.getElementById('etapa-resumen').textContent = paso.resumen || '';
                    var caja = document.getElementById('etapa-chips');
                    caja.innerHTML = '';
                    (paso.opciones || []).forEach(function (nombre) {
                        caja.appendChild(chip(nombre, function () {
                            guardarNota(card.dataset.caja, nombre);
                        }));
                    });
                    var bloque = document.getElementById('etapa-tiempo-bloque');
                    var tiempos = document.getElementById('etapa-tiempos');
                    tiempos.innerHTML = '';
                    bloque.hidden = !(paso.tiempos || []).length;
                    (paso.tiempos || []).forEach(function (tiempo) {
                        tiempos.appendChild(chip(tiempo.etiqueta, function () {
                            guardarNota(card.dataset.caja, tiempo.texto);
                        }));
                    });
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
                    var mov = document.getElementById('etapa-movimiento');
                    var enviar = document.getElementById('etapa-enviar');
                    var entrega = document.getElementById('etapa-entrega');
                    var paso = pasos[card.dataset.etapa] || {};
                    if (card.dataset.destino) {
                        texto(mov, [{ strong: paso.tramo || (card.dataset.fase + ' a ' + card.dataset.destinoNombre) }]);
                        enviar.hidden = false;
                        enviar.textContent = 'Pasar a ' + card.dataset.destinoNombre;
                        entrega.hidden = true;
                    } else {
                        texto(mov, [{ strong: paso.tramo || 'Entrega' }]);
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

                function guardarNota(id, textoNota) {
                    var aviso = document.getElementById('etapa-anota-error');
                    aviso.hidden = true;
                    fetch(raiz.dataset.anotar, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ caja: id, texto: textoNota })
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
                    pintarEtapa(elegido);
                    if (!etapa.open) {
                        etapa.showModal();
                    }
                }

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
            })();
        </script>
    @endpush
@endonce
