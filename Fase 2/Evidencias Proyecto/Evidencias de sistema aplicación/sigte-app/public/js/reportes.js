document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.reportes-tab');
    const panels = document.querySelectorAll('.reportes-panel');
    const tabOculta = document.getElementById('reportes-tab-seleccionada');
    const disponibles = ['produccion', 'trazabilidad', 'entregas'];

    function seleccionarTab(nombre, actualizarUrl = false) {
        const tabElegida = disponibles.includes(nombre) ? nombre : 'produccion';

        // Mostrar únicamente las descripciones de la pestaña seleccionada.
        document.querySelectorAll('[data-tab-descripcion]').forEach(function (elemento) {
            elemento.hidden = elemento.dataset.tabDescripcion !== tabElegida;
        });

        const tipo = document.querySelector('.reportes-filtro-tipo');
        const servicio = document.querySelector('.reportes-filtro-servicio');
        if (tipo) tipo.hidden = tabElegida !== 'trazabilidad';
        if (servicio) servicio.hidden = tabElegida !== 'produccion';
        if (tabOculta) tabOculta.value = tabElegida;
        tabs.forEach(function (tab) {
            const activo = tab.dataset.tab === tabElegida;
            tab.classList.toggle('activo', activo);
            tab.setAttribute('aria-selected', String(activo));
            tab.tabIndex = activo ? 0 : -1;
        });
        panels.forEach(function (panel) {
            const activo = panel.id === 'panel-' + tabElegida;
            panel.classList.toggle('activo', activo);
            panel.hidden = !activo;
        });
        if (actualizarUrl) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabElegida);
            window.history.replaceState(null, '', url);
            document.querySelectorAll('.reportes-paginacion a[href]').forEach(function (enlace) {
                const destino = new URL(enlace.href, window.location.href);
                destino.searchParams.set('tab', tabElegida);
                enlace.href = destino.toString();
            });
        }
    }

    seleccionarTab(tabOculta ? tabOculta.value : 'produccion');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            seleccionarTab(tab.dataset.tab, true);
        });
    });

    // Descargar mediante fetch para poder confirmar la respuesta y comunicar cualquier error.
    // El enlace conserva href como alternativa si JavaScript no llega a ejecutarse.
    const formularioDescarga = document.getElementById('reportes-filtros-form');
    const avisoDescarga = document.getElementById('reportes-aviso-descarga');
    const tituloAviso = document.getElementById('reportes-aviso-titulo');
    const mensajeAviso = document.getElementById('reportes-aviso-mensaje');
    const iconoAviso = document.getElementById('reportes-aviso-icono');
    const cerrarAviso = document.getElementById('reportes-aviso-cerrar');
    let descargaActiva = false;

    function mostrarAviso(estado, titulo, mensaje) {
        if (!avisoDescarga) return;
        avisoDescarga.hidden = false;
        avisoDescarga.dataset.estado = estado;
        if (tituloAviso) tituloAviso.textContent = titulo;
        if (mensajeAviso) mensajeAviso.textContent = mensaje;
        if (iconoAviso) iconoAviso.textContent = estado === 'exito' ? '✓' : (estado === 'error' ? '!' : '…');
    }

    if (cerrarAviso) {
        cerrarAviso.addEventListener('click', function () {
            if (avisoDescarga) avisoDescarga.hidden = true;
        });
    }

    document.querySelectorAll('[data-descargar-reporte]').forEach(function (enlace) {
        enlace.addEventListener('click', async function (evento) {
            // Si no hay fetch, el enlace funciona de la manera tradicional.
            if (!formularioDescarga || !window.fetch) return;
            evento.preventDefault();
            if (descargaActiva) return;

            const tipo = enlace.dataset.descargarReporte; // excel | pdf
            const etiqueta = tipo === 'pdf' ? 'PDF' : 'Excel';
            const parametros = new FormData(formularioDescarga);
            const url = new URL(enlace.href, window.location.href);
            ['desde', 'hasta', 'servicio', 'tipo', 'tab'].forEach(function (clave) {
                const valor = String(parametros.get(clave) ?? '');
                if (valor !== '') url.searchParams.set(clave, valor);
                else url.searchParams.delete(clave);
            });

            descargaActiva = true;
            document.querySelectorAll('[data-descargar-reporte]').forEach(function (boton) {
                boton.setAttribute('aria-disabled', 'true');
            });
            mostrarAviso('proceso', 'Generando ' + etiqueta + '…', 'Consultando los registros para preparar el reporte.');

            try {
                const respuesta = await fetch(url.toString(), {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!respuesta.ok) {
                    let detalle = '';
                    const contentType = respuesta.headers.get('Content-Type') || '';
                    if (contentType.includes('application/json')) {
                        const errorJson = await respuesta.json().catch(function () {
                            return null;
                        });
                        detalle = errorJson && errorJson.message ? String(errorJson.message) : '';
                    }
                    if (respuesta.status === 401 || respuesta.status === 419) {
                        detalle = 'Tu sesión puede haber expirado. Vuelve a iniciar sesión.';
                    } else if (respuesta.status === 403) {
                        detalle = 'Tu cuenta no tiene permiso para exportar este reporte.';
                    }
                    throw new Error('HTTP ' + respuesta.status + (detalle ? ': ' + detalle.slice(0, 220) : ''));
                }

                const archivo = await respuesta.blob();
                const firma = new Uint8Array(await archivo.slice(0, 5).arrayBuffer());
                const pdfValido = tipo === 'pdf' && firma.length >= 5 &&
                    String.fromCharCode(...firma) === '%PDF-';
                const excelValido = tipo === 'excel' && firma.length >= 2 &&
                    firma[0] === 0x50 && firma[1] === 0x4b;

                if (!pdfValido && !excelValido) {
                    const tipoRespuesta = respuesta.headers.get('Content-Type') || 'desconocido';
                    throw new Error('El servidor no devolvió un archivo ' + etiqueta + ' válido (tipo recibido: ' + tipoRespuesta + ').');
                }

                const pestana = String(parametros.get('tab') || 'produccion').replace(/[^a-z0-9_-]/gi, '');
                const desde = String(parametros.get('desde') || '').replace(/[^0-9-]/g, '');
                const hasta = String(parametros.get('hasta') || '').replace(/[^0-9-]/g, '');
                const extension = tipo === 'excel' ? 'xlsx' : 'pdf';
                const nombreArchivo = 'sigte-' + pestana + '-' + desde + '_' + hasta + '.' + extension;

                const direccionArchivo = URL.createObjectURL(archivo);
                const elemento = document.createElement('a');
                elemento.href = direccionArchivo;
                elemento.download = nombreArchivo;
                elemento.style.display = 'none';
                document.body.appendChild(elemento);
                elemento.click();
                elemento.remove();
                // Dar tiempo al navegador para iniciar la descarga antes de liberar el archivo.
                window.setTimeout(function () {
                    URL.revokeObjectURL(direccionArchivo);
                }, 60000);

                // El navegador no permite confirmar si el usuario guardó el archivo al disco.
                mostrarAviso('exito', 'Reporte ' + etiqueta + ' generado',
                    'El servidor entregó un archivo válido y se solicitó su descarga: ' + nombreArchivo + '. Revisa Descargas.');
            } catch (error) {
                mostrarAviso('error', 'No se pudo exportar ' + etiqueta,
                    error && error.message ? error.message : 'No fue posible comunicarse con el servidor.');
                console.error('SIGTE: fallo de exportación de reportes', error);
            } finally {
                descargaActiva = false;
                document.querySelectorAll('[data-descargar-reporte]').forEach(function (boton) {
                    boton.removeAttribute('aria-disabled');
                });
            }
        });
    });

});
