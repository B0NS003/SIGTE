<?php $__env->startSection('title', 'SIGTE — Mantener catálogo'); ?>

<?php $__env->startSection('content'); ?>
<div class="shell">
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="<?php echo e(asset('images/logo-hospital-circular.png')); ?>" alt="Logo hospital">
            <div>
                <div class="logo">SIG<span>TE</span></div>
                <div class="logo-sub">Administración</div>
            </div>
        </div>
        <?php echo $__env->make('partials.menu-rol', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="sidebar-foot">
            <div class="side-user">
                <div class="avatar"><?php echo e(strtoupper(substr($usuario['nombre'], 0, 1))); ?></div>
                <div>
                    <strong><?php echo e($usuario['nombre']); ?></strong>
                    <small><?php echo e($usuario['rol']); ?></small>
                </div>
            </div>
            <?php echo $__env->make('partials.logout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </aside>

    <main class="main">
        <div class="main-top">
            <div>
                <p class="eyebrow">Gestión · solo administradora</p>
                <h1>Mantener catálogo</h1>
                <p class="main-sub">Las mismas fichas que ve el servicio. Acá se arman, se corrigen y se retiran.</p>
            </div>
            <div class="main-actions">
                <a class="btn btn-ghost" href="<?php echo e(route('mockups.catalogo')); ?>">Ver catálogo de consulta</a>
            </div>
        </div>

        <section class="mant-vitrina">
            <div class="mant-head">
                <div>
                    <h2>Fichas</h2>
                    <p id="fichas-meta"><?php echo e(count($items)); ?> vigentes · <?php echo e(collect($items)->where('guia', false)->count()); ?> sin guía visual</p>
                </div>
                <span class="badge" id="fichas-cuenta"><?php echo e(count($items)); ?></span>
            </div>
            <div class="cat-grid" id="fichas-mural">
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="cat-card mant-card" data-estado="vigente" data-guia="<?php echo e($item['guia'] ? '1' : '0'); ?>">
                        <div class="cat-photo <?php echo e($item['guia'] ? '' : 'is-empty'); ?>">
                            <span class="js-guia-label"><?php echo e($item['guia'] ? 'Con guía visual' : 'Sin guía visual'); ?></span>
                        </div>
                        <div class="cat-body">
                            <strong class="js-codigo"><?php echo e($item['codigo']); ?></strong>
                            <h2 class="js-nombre"><?php echo e($item['nombre']); ?></h2>
                            <p class="js-detalle"><?php echo e($item['servicio']); ?> · <?php echo e($item['tipo']); ?> · <?php echo e($item['piezas']); ?> <?php echo e($item['piezas'] === 1 ? 'pieza' : 'piezas'); ?></p>
                            <div class="mant-acciones">
                                <button class="btn btn-ghost js-editar" type="button">Editar</button>
                                <button class="btn btn-ghost js-retirar" type="button">Retirar</button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <form class="mant-editor ficha-admin" id="ficha-form" action="<?php echo e(route('mockups.catalogo.admin')); ?>" method="get" onsubmit="return false">
            <div class="ficha-layout">
                <section class="ficha-guia is-empty" id="preview-guia" aria-label="Así se ve la ficha">
                    <span class="ficha-kicker" id="preview-kicker">Sin guía visual</span>
                    <strong id="preview-titulo">Así la va a ver el servicio</strong>
                    <p id="preview-texto">Si la caja no tiene foto, igual se puede crear. La lista de piezas alcanza.</p>
                    <div class="ficha-mosaico" id="preview-mosaico"></div>
                </section>
                <section class="panel ficha-datos">
                    <div class="panel-head">
                        <h2 id="ficha-titulo">Nueva ficha</h2>
                        <span class="badge" id="ficha-modo">Alta</span>
                    </div>
                    <p class="adv-ok" id="ficha-ok" role="status" hidden></p>
                    <p class="auth-error" id="ficha-error" role="alert" hidden></p>
                    <div class="mant-datos">
                        <label for="codigo">Código
                            <input id="codigo" name="codigo" type="text" placeholder="SET-XXX-00" autocomplete="off">
                        </label>
                        <label for="nombre">Nombre
                            <input id="nombre" name="nombre" type="text" placeholder="Nombre de la caja" autocomplete="off">
                        </label>
                        <label for="tipo">Tipo
                            <select id="tipo" name="tipo">
                                <option value="">Elige el tipo</option>
                                <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tipo); ?>"><?php echo e($tipo); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>
                        <label for="servicio">Servicio
                            <select id="servicio" name="servicio">
                                <option value="">Elige el servicio</option>
                                <?php $__currentLoopData = $servicios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $servicio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($servicio); ?>"><?php echo e($servicio); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>
                    </div>
                    <div class="ficha-guia-row">
                        <label class="check">
                            <input id="guia" type="checkbox">
                            <span>Tiene guía visual</span>
                        </label>
                        <label class="ficha-foto" for="foto">
                            <span>Foto del set, si está</span>
                            <input id="foto" name="foto" type="file" accept="image/*">
                        </label>
                    </div>
                </section>
            </div>

            <section class="panel ficha-lista">
                <div class="panel-head">
                    <h2>Qué debe contener</h2>
                    <button class="btn btn-ghost" id="agregar-pieza" type="button">Agregar pieza</button>
                </div>
                <div id="piezas"></div>
                <div class="cuenta-pie">
                    <p class="ficha-ayuda">Retirar la deja fuera del catálogo vigente. El historial se mantiene.</p>
                    <div class="recv-actions">
                        <button class="btn btn-ghost" id="ficha-cancelar" type="button" hidden>Cancelar</button>
                        <button class="btn btn-primary" id="ficha-guardar" type="button">Crear ficha</button>
                    </div>
                </div>
            </section>
        </form>
    </main>
</div>
<script>
    var fichas = <?php echo json_encode(collect($items)->keyBy('codigo'), 15, 512) ?>;
    var formFila = null;
    var titulo = document.getElementById('ficha-titulo');
    var modo = document.getElementById('ficha-modo');
    var guardar = document.getElementById('ficha-guardar');
    var cancelar = document.getElementById('ficha-cancelar');
    var ok = document.getElementById('ficha-ok');
    var error = document.getElementById('ficha-error');
    var codigo = document.getElementById('codigo');
    var nombre = document.getElementById('nombre');
    var tipo = document.getElementById('tipo');
    var servicio = document.getElementById('servicio');
    var guia = document.getElementById('guia');
    var foto = document.getElementById('foto');
    var piezas = document.getElementById('piezas');
    var mosaico = document.getElementById('preview-mosaico');

    function aviso(texto, esError) {
        ok.hidden = esError || !texto;
        error.hidden = !esError || !texto;
        (esError ? error : ok).textContent = texto || '';
    }

    function filaPieza(pieza) {
        var fila = document.createElement('div');
        fila.className = 'mant-pieza';
        fila.innerHTML = '<span class="ficha-thumb is-empty js-letra" aria-hidden="true">—</span><input class="pieza-nombre" type="text" placeholder="Instrumento o material" autocomplete="off"><input class="pieza-cant" type="number" min="1" placeholder="Cant."><label class="check"><input class="pieza-foto" type="checkbox"><span>Con foto</span></label><button class="btn btn-ghost js-quitar" type="button">Quitar</button>';
        if (pieza && pieza.nombre) {
            fila.querySelector('.pieza-nombre').value = pieza.nombre;
            fila.querySelector('.pieza-cant').value = pieza.cantidad || '';
            fila.querySelector('.pieza-foto').checked = !!pieza.imagen;
            marcarLetra(fila);
        }
        return fila;
    }

    function marcarLetra(fila) {
        var texto = fila.querySelector('.pieza-nombre').value.trim();
        var letra = fila.querySelector('.js-letra');
        var conFoto = fila.querySelector('.pieza-foto').checked && texto;
        letra.textContent = conFoto ? texto.charAt(0).toUpperCase() : '—';
        letra.classList.toggle('is-empty', !conFoto);
    }

    function pintarPiezas(lista) {
        piezas.replaceChildren();
        (lista && lista.length ? lista : [{}]).forEach(function (pieza) {
            piezas.appendChild(filaPieza(pieza));
        });
        actualizarVista();
    }

    function leerPiezas() {
        return Array.prototype.map.call(piezas.querySelectorAll('.mant-pieza'), function (fila) {
            return {
                nombre: fila.querySelector('.pieza-nombre').value.trim(),
                cantidad: Number(fila.querySelector('.pieza-cant').value),
                imagen: fila.querySelector('.pieza-foto').checked
            };
        }).filter(function (pieza) {
            return pieza.nombre !== '' || pieza.cantidad;
        });
    }

    function actualizarVista() {
        var seccion = document.getElementById('preview-guia');
        var conGuia = guia.checked;
        var tituloCaja = nombre.value.trim();
        seccion.classList.toggle('is-empty', !conGuia);
        document.getElementById('preview-kicker').textContent = conGuia ? 'Guía visual' : 'Sin guía visual';
        document.getElementById('preview-titulo').textContent = tituloCaja || (conGuia ? 'Cómo se reconoce el set' : 'Así la va a ver el servicio');
        document.getElementById('preview-texto').textContent = conGuia
            ? 'Referencia de las piezas que tienen foto.'
            : 'Si la caja no tiene foto, igual se puede crear. La lista de piezas alcanza.';
        mosaico.replaceChildren();
        if (!conGuia) {
            return;
        }
        piezas.querySelectorAll('.mant-pieza').forEach(function (fila) {
            marcarLetra(fila);
            var texto = fila.querySelector('.pieza-nombre').value.trim();
            if (!fila.querySelector('.pieza-foto').checked || !texto) {
                return;
            }
            var figura = document.createElement('figure');
            var thumb = document.createElement('span');
            thumb.className = 'ficha-thumb';
            thumb.textContent = texto.charAt(0).toUpperCase();
            var caption = document.createElement('figcaption');
            caption.textContent = texto;
            figura.appendChild(thumb);
            figura.appendChild(caption);
            mosaico.appendChild(figura);
        });
    }

    function resumir() {
        var cards = document.querySelectorAll('#fichas-mural .mant-card');
        var vigentes = 0;
        var sinGuia = 0;
        var retiradas = 0;
        cards.forEach(function (card) {
            if (card.dataset.estado === 'retirada') {
                retiradas += 1;
                return;
            }
            vigentes += 1;
            if (card.dataset.guia !== '1') {
                sinGuia += 1;
            }
        });
        document.getElementById('fichas-cuenta').textContent = String(cards.length);
        var texto = vigentes + ' vigentes · ' + sinGuia + ' sin guía visual';
        if (retiradas) {
            texto += ' · ' + retiradas + (retiradas === 1 ? ' retirada' : ' retiradas');
        }
        document.getElementById('fichas-meta').textContent = texto;
    }

    function limpiarAlta() {
        if (formFila) {
            formFila.classList.remove('is-editing');
        }
        formFila = null;
        titulo.textContent = 'Nueva ficha';
        modo.textContent = 'Alta';
        guardar.textContent = 'Crear ficha';
        cancelar.hidden = true;
        codigo.value = '';
        nombre.value = '';
        tipo.value = '';
        servicio.value = '';
        guia.checked = false;
        foto.value = '';
        pintarPiezas([{}]);
    }

    function htmlCard() {
        return '<div class="cat-photo is-empty"><span class="js-guia-label">Sin guía visual</span></div><div class="cat-body"><strong class="js-codigo"></strong><h2 class="js-nombre"></h2><p class="js-detalle"></p><div class="mant-acciones"><button class="btn btn-ghost js-editar" type="button">Editar</button><button class="btn btn-ghost js-retirar" type="button">Retirar</button></div></div>';
    }

    function escribirCard(card, datos) {
        var total = datos.instrumentos.reduce(function (suma, pieza) { return suma + Number(pieza.cantidad || 0); }, 0);
        card.dataset.estado = datos.estado || 'vigente';
        card.dataset.guia = datos.guia ? '1' : '0';
        card.classList.toggle('mant-retirada', card.dataset.estado === 'retirada');
        var photo = card.querySelector('.cat-photo');
        var retirada = card.dataset.estado === 'retirada';
        photo.classList.toggle('is-empty', retirada || !datos.guia);
        card.querySelector('.js-guia-label').textContent = retirada ? 'Retirada' : (datos.guia ? 'Con guía visual' : 'Sin guía visual');
        card.querySelector('.js-codigo').textContent = datos.codigo;
        card.querySelector('.js-nombre').textContent = datos.nombre;
        card.querySelector('.js-detalle').textContent = datos.servicio + ' · ' + datos.tipo + ' · ' + total + (total === 1 ? ' pieza' : ' piezas');
    }

    document.getElementById('agregar-pieza').addEventListener('click', function () {
        piezas.appendChild(filaPieza(null));
    });

    piezas.addEventListener('click', function (evento) {
        var quitar = evento.target.closest('.js-quitar');
        if (!quitar) {
            return;
        }
        quitar.closest('.mant-pieza').remove();
        if (!piezas.children.length) {
            pintarPiezas([{}]);
            return;
        }
        actualizarVista();
    });

    document.getElementById('ficha-form').addEventListener('input', actualizarVista);
    document.getElementById('ficha-form').addEventListener('change', actualizarVista);

    document.getElementById('fichas-mural').addEventListener('click', function (evento) {
        var editar = evento.target.closest('.js-editar');
        var retirar = evento.target.closest('.js-retirar');
        if (editar) {
            if (formFila) {
                formFila.classList.remove('is-editing');
            }
            formFila = editar.closest('.mant-card');
            formFila.classList.add('is-editing');
            var clave = formFila.querySelector('.js-codigo').textContent;
            var ficha = fichas[clave];
            codigo.value = clave;
            nombre.value = ficha.nombre;
            tipo.value = ficha.tipo;
            servicio.value = ficha.servicio;
            guia.checked = !!ficha.guia;
            foto.value = '';
            pintarPiezas(ficha.instrumentos);
            titulo.textContent = 'Editar ficha';
            modo.textContent = 'Cambio';
            guardar.textContent = 'Guardar cambios';
            cancelar.hidden = false;
            aviso('', false);
            document.getElementById('ficha-form').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            nombre.focus();
        }
        if (!retirar) {
            return;
        }
        var card = retirar.closest('.mant-card');
        if (card.dataset.estado === 'retirada') {
            return;
        }
        var claveRetiro = card.querySelector('.js-codigo').textContent;
        card.dataset.estado = 'retirada';
        card.classList.add('mant-retirada');
        card.querySelector('.cat-photo').classList.add('is-empty');
        card.querySelector('.js-guia-label').textContent = 'Retirada';
        retirar.disabled = true;
        retirar.textContent = 'Retirada';
        if (fichas[claveRetiro]) {
            fichas[claveRetiro].estado = 'retirada';
        }
        aviso(claveRetiro + ' salió del catálogo vigente. El historial se mantiene.', false);
        resumir();
    });

    cancelar.addEventListener('click', function () {
        limpiarAlta();
        aviso('', false);
    });

    guardar.addEventListener('click', function () {
        var clave = codigo.value.trim().toUpperCase();
        var contenido = leerPiezas();
        if (!clave || !nombre.value.trim() || !tipo.value || !servicio.value) {
            aviso('Completa código, nombre, tipo y servicio.', true);
            return;
        }
        if (!contenido.length || contenido.some(function (pieza) { return !pieza.nombre || !(pieza.cantidad >= 1); })) {
            aviso('Cada pieza necesita nombre y cantidad.', true);
            return;
        }
        var repetida = Array.prototype.some.call(document.querySelectorAll('#fichas-mural .js-codigo'), function (celda) {
            var esLaMisma = formFila && celda.closest('.mant-card') === formFila;
            return !esLaMisma && celda.textContent.toUpperCase() === clave;
        });
        if (repetida) {
            aviso(clave + ' ya está en el catálogo.', true);
            return;
        }
        var datos = {
            codigo: clave,
            nombre: nombre.value.trim(),
            tipo: tipo.value,
            servicio: servicio.value,
            guia: guia.checked,
            instrumentos: contenido,
            estado: formFila ? formFila.dataset.estado : 'vigente'
        };
        if (formFila) {
            var anterior = formFila.querySelector('.js-codigo').textContent;
            if (anterior !== clave) {
                delete fichas[anterior];
            }
            fichas[clave] = datos;
            escribirCard(formFila, datos);
            aviso('Listo. Quedaron los datos y el contenido de ' + clave + '.', false);
            limpiarAlta();
            resumir();
            return;
        }
        var card = document.createElement('article');
        card.className = 'cat-card mant-card';
        card.innerHTML = htmlCard();
        fichas[clave] = datos;
        escribirCard(card, datos);
        document.getElementById('fichas-mural').prepend(card);
        aviso('Listo. ' + clave + ' quedó en el catálogo.', false);
        limpiarAlta();
        resumir();
    });

    pintarPiezas([{}]);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/mockups/catalogo-admin.blade.php ENDPATH**/ ?>