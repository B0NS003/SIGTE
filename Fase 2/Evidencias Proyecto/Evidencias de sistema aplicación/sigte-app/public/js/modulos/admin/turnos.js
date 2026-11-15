// Turnos admin: drag-drop, búsqueda, exportes y grupo activo.
(function () {
    var tablero = document.querySelector('.turno-tablero');
    if (!tablero) {
        return;
    }

    var estado = document.querySelector('.turno-estado');
    var btnGuardar = document.getElementById('turno-guardar');
    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var token = tokenMeta ? tokenMeta.content : '';
    var grupo = tablero.dataset.grupo || 'operador';
    var arrastrada = null;
    var pendientes = 0;

    function normalizar(texto) {
        return (texto || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    var busca = tablero.querySelector('.turno-busca input');
    if (busca) {
        var columnaLibre = busca.closest('.turno-columna');
        var avisoVacio = document.createElement('p');
        avisoVacio.className = 'turno-vacio';
        avisoVacio.textContent = 'Ningún nombre coincide.';
        avisoVacio.hidden = true;
        columnaLibre.querySelector('.turno-lista').before(avisoVacio);
        busca.addEventListener('input', function () {
            var consulta = normalizar(busca.value.trim());
            var visibles = 0;
            columnaLibre.querySelectorAll('.turno-tarjeta').forEach(function (tarjeta) {
                var coincide = consulta === '' || normalizar(tarjeta.textContent).indexOf(consulta) !== -1;
                tarjeta.hidden = !coincide;
                if (coincide) {
                    visibles += 1;
                }
            });
            avisoVacio.hidden = visibles > 0;
        });
    }

    var avisoTimer = null;
    function avisar(texto, segundos) {
        estado.hidden = false;
        estado.textContent = texto;
        if (avisoTimer) {
            clearTimeout(avisoTimer);
        }
        if (segundos) {
            avisoTimer = setTimeout(function () {
                estado.hidden = true;
                estado.textContent = '';
                avisoTimer = null;
            }, segundos * 1000);
        }
    }

    function etiquetaGuardar(texto) {
        var span = btnGuardar.querySelector('span');
        if (span) {
            span.textContent = texto;
        }
    }

    function marcarPendiente(si) {
        if (si) {
            pendientes += 1;
            btnGuardar.classList.add('is-pendiente');
            etiquetaGuardar('Guardar cambios');
        } else {
            pendientes = Math.max(0, pendientes - 1);
            if (pendientes === 0) {
                btnGuardar.classList.remove('is-pendiente');
            }
        }
    }

    tablero.querySelectorAll('.turno-tarjeta').forEach(function (tarjeta) {
        tarjeta.addEventListener('dragstart', function () {
            arrastrada = tarjeta;
            tarjeta.classList.add('is-arrastrando');
        });
        tarjeta.addEventListener('dragend', function () {
            tarjeta.classList.remove('is-arrastrando');
            arrastrada = null;
        });
    });

    tablero.querySelectorAll('.turno-columna').forEach(function (columna) {
        columna.addEventListener('dragover', function (evento) {
            evento.preventDefault();
            columna.classList.add('is-sobre');
        });
        columna.addEventListener('dragleave', function () {
            columna.classList.remove('is-sobre');
        });
        columna.addEventListener('drop', function (evento) {
            evento.preventDefault();
            columna.classList.remove('is-sobre');
            if (!arrastrada) {
                return;
            }
            var lista = columna.querySelector('.turno-lista');
            var turno = columna.dataset.turno || null;
            var anterior = arrastrada.parentElement;
            lista.appendChild(arrastrada);
            avisar('Guardando…');
            marcarPendiente(true);
            fetch(tablero.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({
                    personal_id: arrastrada.dataset.id,
                    turno: turno,
                    grupo: grupo,
                }),
            }).then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error();
                }
                avisar('Turno actualizado.');
                marcarPendiente(false);
                if (busca) {
                    busca.dispatchEvent(new Event('input'));
                }
            }).catch(function () {
                anterior.appendChild(arrastrada);
                avisar('No se pudo guardar. Vuelve a soltar el nombre.');
                marcarPendiente(false);
            });
        });
    });

    if (btnGuardar) {
        btnGuardar.addEventListener('click', function () {
            if (pendientes > 0) {
                avisar('Todavía hay cambios guardándose…');
                return;
            }
            avisar('Turno guardado.');
            etiquetaGuardar('Turno guardado');
            setTimeout(function () {
                etiquetaGuardar('Guardar cambios');
            }, 1800);
        });
    }

    document.querySelectorAll('[data-descargar-reporte]').forEach(function (enlace) {
        enlace.addEventListener('click', function (evento) {
            if (pendientes > 0) {
                evento.preventDefault();
                evento.stopImmediatePropagation();
                avisar('Espera a que terminen de guardarse los cambios antes de exportar.', 3);
            }
        }, true);
    });
})();
