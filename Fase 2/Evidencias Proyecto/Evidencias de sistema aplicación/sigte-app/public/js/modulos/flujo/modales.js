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
                var modoActual = 'pasar';

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
                    bloque.hidden = filas.length === 0 || modoActual !== 'anotar';
                    document.getElementById('etapa-historial-total').textContent = filas.length ? '(' + filas.length + ')' : '';
                    filas.slice().reverse().forEach(function (nota) {
                        var meta = nota.cuando;
                        if (nota.quien) {
                            meta += ' · ' + nota.quien;
                        }
                        lista.appendChild(fila(nota.texto, meta));
                    });
                }

                function pintarResumen(id) {
                    var lista = document.getElementById('etapa-resumen-lista');
                    var vacio = document.getElementById('etapa-resumen-vacio');
                    var filas = notasDeEtapa(id);
                    lista.innerHTML = '';
                    vacio.hidden = filas.length > 0;
                    filas.forEach(function (nota) {
                        var meta = hora(nota);
                        if (nota.quien) {
                            meta += ' · ' + nota.quien;
                        }
                        lista.appendChild(fila(nota.texto, meta));
                    });
                }

                function puedeAnotar(card) {
                    if (!card) {
                        return false;
                    }
                    var paso = pasos[card.dataset.etapa] || {};
                    return (paso.opciones || []).length > 0 || (paso.tiempos || []).length > 0;
                }

                function aplicarVista() {
                    var anotarSec = document.getElementById('etapa-anotar');
                    var pasarSec = document.getElementById('etapa-pasar');
                    var resumenSec = document.getElementById('etapa-resumen');
                    var historial = document.getElementById('etapa-historial');
                    var irTexto = document.getElementById('etapa-ir-pasar-texto');
                    if (!anotarSec || !pasarSec || !historial) {
                        return;
                    }
                    var conAnotar = puedeAnotar(cardActual);
                    var modo = modoActual === 'anotar' && conAnotar ? 'anotar' : 'pasar';
                    modoActual = modo;
                    etapa.dataset.modo = modo;

                    if (irTexto) {
                        if (cardActual && cardActual.dataset.destino) {
                            irTexto.textContent = 'Pasar a ' + cardActual.dataset.destinoNombre;
                        } else {
                            irTexto.textContent = 'Registrar la entrega';
                        }
                    }

                    if (modo === 'anotar') {
                        anotarSec.hidden = false;
                        pasarSec.hidden = true;
                        if (resumenSec) {
                            resumenSec.hidden = true;
                        }
                        historial.open = true;
                        if (cardActual) {
                            pintarLista(cardActual.dataset.caja);
                        }
                    } else {
                        anotarSec.hidden = true;
                        pasarSec.hidden = false;
                        if (resumenSec) {
                            resumenSec.hidden = !conAnotar;
                        }
                        pasarSec.classList.toggle('is-solo', !conAnotar);
                        historial.hidden = true;
                        historial.open = false;
                        if (cardActual && resumenSec) {
                            pintarResumen(cardActual.dataset.caja);
                        }
                    }
                    actualizarPendiente();
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
                    var sugerido = tiempo.sugerido && tiempo.sugerido >= 1 ? String(tiempo.sugerido) : '';
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
                    if (!hecho && sugerido) {
                        input.value = sugerido;
                    }
                    input.placeholder = sugerido || '—';
                    unidad.textContent = 'min';
                    boton.type = 'button';
                    boton.className = 'flujo-chip' + (hecho ? ' is-hecho' : '');
                    boton.textContent = hecho ? 'Anotar otra vez' : 'Anotar';
                    if (hecho) {
                        boton.setAttribute('aria-label', tiempo.etiqueta + ', ya anotado. Anotar otra vez');
                    }
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
                    pintarLista(id);
                    bloquearSinPuesto();
                    aplicarVista();
                    var enfocar = foco ? etapa.querySelector(foco) : null;
                    if (enfocar && !enfocar.disabled && modoActual === 'anotar') {
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

                function avisarSinPuesto(enAnotar) {
                    var texto = 'Elige qué cuenta de operadora está en el puesto antes de registrar.';
                    if (enAnotar) {
                        mostrarError(texto);
                    } else {
                        var aviso = document.getElementById('etapa-error');
                        aviso.hidden = false;
                        aviso.textContent = texto;
                    }
                }

                function bloquearSinPuesto() {
                    // Los campos siguen editables: si falta puesto, se avisa al guardar sin borrar lo escrito.
                    var entrega = document.getElementById('etapa-entrega');
                    if (sinPuesto) {
                        entrega.setAttribute('aria-disabled', 'true');
                    } else {
                        entrega.removeAttribute('aria-disabled');
                    }
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
                    if (sinPuesto) {
                        avisarSinPuesto(true);
                        return Promise.resolve(false);
                    }
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
                    modoActual = modo === 'anotar' ? 'anotar' : 'pasar';
                    if (!conservarError) {
                        document.getElementById('etapa-error').hidden = true;
                    }
                    mostrarError('');
                    pintarEtapa(elegido);
                    if (!etapa.open) {
                        etapa.showModal();
                    }
                    var foco = modoActual === 'anotar'
                        ? etapa.querySelector('#etapa-chips .flujo-chip:not(:disabled), #etapa-tiempos .flujo-minutos:not(:disabled)')
                        : (enviar.hidden ? document.getElementById('etapa-entrega') : enviar);
                    if (foco && !foco.disabled) {
                        foco.focus();
                    }
                }

                formEtapa.addEventListener('submit', function (evento) {
                    if (sinPuesto) {
                        evento.preventDefault();
                        avisarSinPuesto(false);
                        return;
                    }
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

                var irPasar = document.getElementById('etapa-ir-pasar');
                if (irPasar) {
                    irPasar.addEventListener('click', function () {
                        modoActual = 'pasar';
                        aplicarVista();
                        var foco = enviar.hidden ? document.getElementById('etapa-entrega') : enviar;
                        if (foco && !foco.disabled) {
                            foco.focus();
                        }
                    });
                }

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
                    if (!retro.open) {
                        retro.showModal();
                    }
                }

                document.getElementById('retro-pedir').addEventListener('submit', function (evento) {
                    if (!sinPuesto) {
                        return;
                    }
                    evento.preventDefault();
                    var error = document.getElementById('retro-error');
                    error.hidden = false;
                    error.textContent = 'Elige qué cuenta de operadora está en el puesto antes de registrar.';
                });

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
