/**
 * Descarga PDF/Excel con aviso fijo: progreso, éxito o error.
 * Enlaces: [data-descargar-reporte="pdf|excel"]. Opcional: #reportes-filtros-form.
 */
(function () {
    function listo(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    listo(function () {
        var enlaces = document.querySelectorAll('[data-descargar-reporte]');
        if (!enlaces.length || !window.fetch) {
            return;
        }

        var aviso = document.getElementById('exportar-aviso');
        var tituloAviso = document.getElementById('exportar-aviso-titulo');
        var mensajeAviso = document.getElementById('exportar-aviso-mensaje');
        var iconoAviso = document.getElementById('exportar-aviso-icono');
        var cerrarAviso = document.getElementById('exportar-aviso-cerrar');
        var formulario = document.getElementById('reportes-filtros-form');
        var descargaActiva = false;
        var ocultarTimer = null;

        function mostrarAviso(estado, titulo, mensaje) {
            if (!aviso) {
                return;
            }
            if (ocultarTimer) {
                window.clearTimeout(ocultarTimer);
                ocultarTimer = null;
            }
            aviso.hidden = false;
            aviso.dataset.estado = estado;
            if (tituloAviso) {
                tituloAviso.textContent = titulo;
            }
            if (mensajeAviso) {
                mensajeAviso.textContent = mensaje;
            }
            if (iconoAviso) {
                iconoAviso.textContent = estado === 'exito' ? '✓' : (estado === 'error' ? '!' : '…');
            }
            if (estado === 'exito' || estado === 'error') {
                ocultarTimer = window.setTimeout(function () {
                    aviso.hidden = true;
                }, estado === 'exito' ? 7000 : 9000);
            }
        }

        if (cerrarAviso && aviso) {
            cerrarAviso.addEventListener('click', function () {
                aviso.hidden = true;
                if (ocultarTimer) {
                    window.clearTimeout(ocultarTimer);
                    ocultarTimer = null;
                }
            });
        }

        function nombreDesdeCabecera(cabecera, respaldo) {
            if (!cabecera) {
                return respaldo;
            }
            var utf = /filename\*=UTF-8''([^;]+)/i.exec(cabecera);
            if (utf && utf[1]) {
                try {
                    return decodeURIComponent(utf[1].trim());
                } catch (e) {
                    // Seguir con filename clásico.
                }
            }
            var clasico = /filename=\"?([^\";]+)\"?/i.exec(cabecera);
            return clasico && clasico[1] ? clasico[1].trim() : respaldo;
        }

        function armarUrl(enlace) {
            var url = new URL(enlace.href, window.location.href);
            if (!formulario) {
                return url;
            }
            var parametros = new FormData(formulario);
            ['desde', 'hasta', 'servicio', 'tipo', 'tab'].forEach(function (clave) {
                var valor = String(parametros.get(clave) ?? '');
                if (valor !== '') {
                    url.searchParams.set(clave, valor);
                } else {
                    url.searchParams.delete(clave);
                }
            });
            return url;
        }

        function nombreRespaldo(tipo, url) {
            var extension = tipo === 'excel' ? 'xlsx' : 'pdf';
            var pestana = String(url.searchParams.get('tab') || 'reporte').replace(/[^a-z0-9_-]/gi, '');
            var desde = String(url.searchParams.get('desde') || '').replace(/[^0-9-]/g, '');
            var hasta = String(url.searchParams.get('hasta') || '').replace(/[^0-9-]/g, '');
            if (desde && hasta) {
                return 'sigte-' + pestana + '-' + desde + '_' + hasta + '.' + extension;
            }
            return 'sigte-' + (tipo === 'excel' ? 'excel' : 'pdf') + '.' + extension;
        }

        enlaces.forEach(function (enlace) {
            enlace.addEventListener('click', async function (evento) {
                evento.preventDefault();
                if (descargaActiva) {
                    return;
                }

                var tipo = enlace.dataset.descargarReporte === 'pdf' ? 'pdf' : 'excel';
                var etiqueta = tipo === 'pdf' ? 'PDF' : 'Excel';
                var url = armarUrl(enlace);

                descargaActiva = true;
                document.querySelectorAll('[data-descargar-reporte]').forEach(function (boton) {
                    boton.setAttribute('aria-disabled', 'true');
                });
                mostrarAviso('proceso', 'Generando ' + etiqueta + '…', 'Consultando los registros para preparar el archivo.');

                try {
                    var respuesta = await fetch(url.toString(), {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/octet-stream',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!respuesta.ok) {
                        var detalle = '';
                        var contentType = respuesta.headers.get('Content-Type') || '';
                        if (contentType.indexOf('application/json') !== -1) {
                            var errorJson = await respuesta.json().catch(function () {
                                return null;
                            });
                            detalle = errorJson && errorJson.message ? String(errorJson.message) : '';
                        }
                        if (respuesta.status === 401 || respuesta.status === 419) {
                            detalle = 'Tu sesión puede haber expirado. Vuelve a iniciar sesión.';
                        } else if (respuesta.status === 403) {
                            detalle = 'Tu cuenta no tiene permiso para exportar este archivo.';
                        }
                        throw new Error(detalle || ('No se pudo generar el ' + etiqueta + ' (error ' + respuesta.status + ').'));
                    }

                    var archivo = await respuesta.blob();
                    var firma = new Uint8Array(await archivo.slice(0, 5).arrayBuffer());
                    var pdfValido = tipo === 'pdf' && firma.length >= 5 &&
                        String.fromCharCode(firma[0], firma[1], firma[2], firma[3], firma[4]) === '%PDF-';
                    var excelValido = tipo === 'excel' && firma.length >= 2 &&
                        firma[0] === 0x50 && firma[1] === 0x4b;

                    if (!pdfValido && !excelValido) {
                        throw new Error('El servidor no devolvió un archivo ' + etiqueta + ' válido.');
                    }

                    var nombreArchivo = nombreDesdeCabecera(
                        respuesta.headers.get('Content-Disposition'),
                        nombreRespaldo(tipo, url)
                    );

                    var direccionArchivo = URL.createObjectURL(archivo);
                    var elemento = document.createElement('a');
                    elemento.href = direccionArchivo;
                    elemento.download = nombreArchivo;
                    elemento.style.display = 'none';
                    document.body.appendChild(elemento);
                    elemento.click();
                    elemento.remove();
                    window.setTimeout(function () {
                        URL.revokeObjectURL(direccionArchivo);
                    }, 60000);

                    mostrarAviso(
                        'exito',
                        etiqueta + ' listo',
                        'Se solicitó la descarga de ' + nombreArchivo + '. Revisa la carpeta Descargas.'
                    );
                } catch (error) {
                    mostrarAviso(
                        'error',
                        'No se pudo exportar ' + etiqueta,
                        error && error.message ? error.message : 'No fue posible comunicarse con el servidor.'
                    );
                } finally {
                    descargaActiva = false;
                    document.querySelectorAll('[data-descargar-reporte]').forEach(function (boton) {
                        boton.removeAttribute('aria-disabled');
                    });
                }
            });
        });
    });
})();
