(function () {
    var abierta = null;

    function mensajeDe(campo) {
        if (campo.disabled || campo.type === 'hidden' || campo.validity.valid) {
            return '';
        }
        if (campo.tagName === 'SELECT' && campo.validity.valueMissing) {
            return 'Elige una opción de la lista.';
        }
        if (campo.type === 'number' && (campo.validity.valueMissing || campo.validity.badInput || campo.validity.rangeUnderflow || campo.validity.rangeOverflow || campo.validity.stepMismatch)) {
            return 'Ingresa una cantidad válida.';
        }
        if (campo.validity.valueMissing) {
            return 'Completa este campo.';
        }
        if (campo.validity.typeMismatch) {
            return 'Revisa el formato de este campo.';
        }
        return 'Revisa este campo.';
    }

    function cajaDe(campo) {
        return campo.closest('.field') || campo.parentElement;
    }

    function limpiar(campo) {
        var caja = cajaDe(campo);
        if (caja) {
            caja.classList.remove('is-invalido', 'entrega-campo-invalido');
        }
        var aviso = campo.sigteAviso;
        if (aviso) {
            aviso.hidden = true;
            aviso.textContent = '';
        }
    }

    function mostrar(campo) {
        var texto = mensajeDe(campo);
        if (!texto) {
            limpiar(campo);
            return;
        }
        var caja = cajaDe(campo);
        if (caja) {
            caja.classList.add('is-invalido', 'entrega-campo-invalido');
        }
        var aviso = campo.sigteAviso;
        if (!aviso) {
            aviso = document.createElement('p');
            aviso.className = 'sigte-error entrega-error-campo';
            aviso.setAttribute('role', 'alert');
            (caja || campo).insertAdjacentElement('afterend', aviso);
            campo.sigteAviso = aviso;
        }
        aviso.hidden = false;
        aviso.textContent = texto;
    }

    document.addEventListener('invalid', function (evento) {
        evento.preventDefault();
        mostrar(evento.target);
    }, true);

    document.addEventListener('input', function (evento) {
        if (evento.target && evento.target.validity) {
            limpiar(evento.target);
        }
    });

    document.addEventListener('change', function (evento) {
        if (evento.target && evento.target.validity) {
            limpiar(evento.target);
        }
    });

    function cerrar() {
        if (!abierta) {
            return;
        }
        abierta.panel.hidden = true;
        abierta.boton.setAttribute('aria-expanded', 'false');
        abierta = null;
    }

    function colocar(lista) {
        var campo = lista.boton.closest('.field') || lista.raiz;
        var caja = campo.getBoundingClientRect();
        lista.panel.style.left = caja.left + 'px';
        lista.panel.style.width = caja.width + 'px';
        lista.panel.style.top = (caja.bottom + 6) + 'px';
    }

    function pintar(lista) {
        var elegido = lista.select.options[lista.select.selectedIndex];
        var texto = elegido ? elegido.text : 'Elige una opción';
        var vacio = !lista.select.value;
        lista.boton.classList.toggle('is-vacio', vacio);
        lista.boton.firstChild.textContent = texto;
        lista.boton.setAttribute('aria-label', texto);
        Array.prototype.forEach.call(lista.panel.querySelectorAll('.sigte-lista-opcion'), function (opcion) {
            opcion.classList.toggle('is-activa', opcion.dataset.valor === lista.select.value);
        });
    }

    function normalizar(texto) {
        return (texto || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function filtrar(lista) {
        if (!lista.busca) {
            return;
        }
        var consulta = normalizar(lista.busca.value.trim());
        var visibles = 0;
        Array.prototype.forEach.call(lista.panel.querySelectorAll('.sigte-lista-opcion'), function (opcion) {
            if (opcion.getAttribute('aria-disabled') === 'true') {
                opcion.hidden = consulta !== '';
                return;
            }
            var coincide = consulta === '' || normalizar(opcion.textContent).indexOf(consulta) !== -1;
            opcion.hidden = !coincide;
            if (coincide) {
                visibles += 1;
            }
        });
        if (lista.vacio) {
            lista.vacio.hidden = visibles > 0;
        }
    }

    function abrir(lista) {
        if (abierta && abierta !== lista) {
            cerrar();
        }
        lista.panel.hidden = false;
        lista.boton.setAttribute('aria-expanded', 'true');
        abierta = lista;
        colocar(lista);
        if (lista.busca) {
            lista.busca.value = '';
            filtrar(lista);
            lista.busca.focus();
        }
    }

    function armar(select) {
        if (select.multiple || select.dataset.sigteLista === '1') {
            return;
        }
        select.dataset.sigteLista = '1';
        var raiz = document.createElement('div');
        raiz.className = 'sigte-lista';
        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'sigte-lista-boton';
        boton.setAttribute('aria-haspopup', 'listbox');
        boton.setAttribute('aria-expanded', 'false');
        boton.appendChild(document.createTextNode(''));
        boton.insertAdjacentHTML('beforeend', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9.5 12 15.5 18 9.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>');
        var panel = document.createElement('ul');
        panel.className = 'sigte-lista-panel';
        panel.hidden = true;
        panel.setAttribute('role', 'listbox');
        if (select.hasAttribute('data-sigte-buscar')) {
            var buscaItem = document.createElement('li');
            buscaItem.className = 'sigte-lista-busca';
            var busca = document.createElement('input');
            busca.type = 'text';
            busca.className = 'sigte-lista-busca-input';
            busca.placeholder = 'Escribe un nombre';
            busca.setAttribute('aria-label', 'Buscar por nombre');
            busca.autocomplete = 'off';
            busca.addEventListener('click', function (evento) {
                evento.stopPropagation();
            });
            busca.addEventListener('input', function () {
                filtrar(lista);
            });
            busca.addEventListener('keydown', function (evento) {
                if (evento.key !== 'Enter') {
                    return;
                }
                evento.preventDefault();
                var primera = lista.panel.querySelector('.sigte-lista-opcion:not([hidden]):not([aria-disabled="true"])');
                if (primera) {
                    primera.click();
                }
            });
            buscaItem.appendChild(busca);
            panel.appendChild(buscaItem);
            var vacio = document.createElement('li');
            vacio.className = 'sigte-lista-vacio';
            vacio.textContent = 'Ningún nombre coincide.';
            vacio.hidden = true;
            panel.appendChild(vacio);
        }
        Array.prototype.forEach.call(select.options, function (opcion) {
            var item = document.createElement('li');
            item.className = 'sigte-lista-opcion';
            item.setAttribute('role', 'option');
            item.dataset.valor = opcion.value;
            item.textContent = opcion.text;
            if (opcion.disabled) {
                item.setAttribute('aria-disabled', 'true');
            }
            item.addEventListener('mousedown', function (evento) {
                evento.preventDefault();
            });
            item.addEventListener('click', function (evento) {
                evento.preventDefault();
                evento.stopPropagation();
                if (opcion.disabled) {
                    return;
                }
                lista.eligiendo = true;
                select.value = opcion.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                pintar(lista);
                cerrar();
            });
            panel.appendChild(item);
        });
        raiz.appendChild(boton);
        raiz.appendChild(panel);
        select.classList.add('sigte-lista-nativa');
        select.parentNode.insertBefore(raiz, select);
        raiz.appendChild(select);
        var lista = {
            raiz: raiz,
            boton: boton,
            panel: panel,
            select: select,
            eligiendo: false,
            busca: raiz.querySelector('.sigte-lista-busca-input'),
            vacio: raiz.querySelector('.sigte-lista-vacio')
        };
        pintar(lista);
        boton.addEventListener('click', function (evento) {
            if (lista.eligiendo) {
                lista.eligiendo = false;
                evento.preventDefault();
                evento.stopPropagation();
                return;
            }
            if (panel.hidden) {
                abrir(lista);
            } else {
                cerrar();
            }
        });
    }

    document.addEventListener('click', function (evento) {
        if (!abierta) {
            return;
        }
        if (!abierta.raiz.contains(evento.target)) {
            cerrar();
        }
    });

    window.addEventListener('resize', function () {
        if (abierta) {
            colocar(abierta);
        }
    });
    window.addEventListener('scroll', function () {
        if (abierta) {
            colocar(abierta);
        }
    }, true);

    document.querySelectorAll('select').forEach(armar);

    document.querySelectorAll('.btn-ver-clave').forEach(function (boton) {
        var campo = boton.parentElement && boton.parentElement.querySelector('input');
        if (!campo) {
            return;
        }
        boton.addEventListener('click', function () {
            var abrir = campo.type === 'password';
            campo.type = abrir ? 'text' : 'password';
            boton.classList.toggle('is-abierto', abrir);
            boton.setAttribute('aria-pressed', abrir ? 'true' : 'false');
            boton.setAttribute('aria-label', abrir ? 'Ocultar contraseña' : 'Mostrar contraseña');
            boton.classList.remove('is-parpadeo');
            void boton.offsetWidth;
            boton.classList.add('is-parpadeo');
        });
    });
})();
