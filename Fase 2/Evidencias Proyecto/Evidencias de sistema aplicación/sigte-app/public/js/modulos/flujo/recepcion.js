(function () {
  const CLAVE_BORRADOR = 'sigte.recepcion.borrador';
  const tipos = JSON.parse(document.getElementById('recv-datos-tipos').textContent);
  const fichas = JSON.parse(document.getElementById('recv-datos-fichas').textContent);
  const antiguo = JSON.parse(document.getElementById('recv-datos-old').textContent);
  const form = document.getElementById('recepcion-form');
  const servicio = document.getElementById('recv-servicio');
  const tipo = document.getElementById('recv-tipo');
  const ficha = document.getElementById('recv-ficha');
  const quien = document.getElementById('recv-quien');
  const obs = document.getElementById('recv-obs');
  const guardar = document.getElementById('recv-guardar');

  function leerBorrador() {
    try {
      return JSON.parse(sessionStorage.getItem(CLAVE_BORRADOR) || 'null');
    } catch (error) {
      return null;
    }
  }

  function guardarBorrador() {
    const datos = {
      servicio_id: servicio.value || '',
      tipo_conjunto_id: tipo.value || '',
      ficha_id: ficha.value || '',
      quien_entrega: quien.value || '',
      obs: obs ? obs.value : '',
    };
    const vacio = !datos.servicio_id && !datos.tipo_conjunto_id && !datos.ficha_id && !datos.quien_entrega.trim() && !datos.obs.trim();
    if (vacio) {
      sessionStorage.removeItem(CLAVE_BORRADOR);
      return;
    }
    sessionStorage.setItem(CLAVE_BORRADOR, JSON.stringify(datos));
  }

  function llenar(select, datos, texto, etiqueta) {
    select.replaceChildren(new Option(texto, ''));
    datos.forEach((d) => select.add(new Option(etiqueta(d), String(d.id))));
  }

  function cambiarServicio() {
    const id = Number(servicio.value);
    const filtrados = tipos.filter((t) => Number(t.servicio_id) === id);
    const vacio = id
      ? (filtrados.length ? 'Selecciona un tipo de conjunto...' : 'Este servicio no tiene tipos en el catálogo')
      : 'Primero selecciona el servicio...';
    llenar(tipo, filtrados, vacio, (t) => t.nombre);
    tipo.disabled = !id || filtrados.length === 0;
    cambiarTipo();
  }

  function cambiarTipo() {
    const id = Number(tipo.value);
    const sid = Number(servicio.value);
    const filtradas = fichas.filter((f) => Number(f.tipo_conjunto_id) === id && Number(f.servicio_id) === sid);
    const vacio = id
      ? (filtradas.length ? 'Selecciona una caja del catálogo...' : 'No hay cajas para este tipo')
      : 'Primero selecciona el tipo de conjunto...';
    llenar(ficha, filtradas, vacio, (f) => f.codigo + ' — ' + f.nombre + ' (' + f.piezas + ' piezas)');
    ficha.disabled = !id || filtradas.length === 0;
    cambiarFicha();
  }

  function cambiarFicha() {
    const f = fichas.find((x) => String(x.id) === ficha.value);
    quien.disabled = !f;
    document.getElementById('recv-resumen-vacio').hidden = !!f;
    document.getElementById('recv-resumen-datos').hidden = !f;
    if (f) {
      const t = tipos.find((x) => String(x.id) === String(f.tipo_conjunto_id));
      const s = servicio.options[servicio.selectedIndex];
      for (const [id, valor] of Object.entries({
        'recv-nombre': f.nombre,
        'recv-codigo': f.codigo,
        'recv-servicio-nombre': s.text,
        'recv-tipo-nombre': t?.nombre || '—',
        'recv-piezas': f.piezas,
        'recv-guia': f.guia ? 'Disponible' : 'Sin guía visual',
      })) {
        document.getElementById(id).textContent = valor;
      }
    }
    actualizar();
  }

  function actualizar() {
    const paso = !servicio.value ? 1 : !tipo.value ? 2 : !ficha.value ? 3 : 4;
    document.getElementById('recv-paso').textContent = 'Paso ' + paso + ' de 4';
    document.getElementById('recv-progreso-barra').style.width = (paso * 25) + '%';
    [['recv-grupo-tipo', !servicio.value], ['recv-grupo-ficha', !tipo.value], ['recv-grupo-entrega', !ficha.value]].forEach(([id, b]) => {
      document.getElementById(id).classList.toggle('is-bloqueado', b);
    });
    guardar.disabled = !servicio.value || !tipo.value || !ficha.value || !quien.value.trim();
  }

  function aplicarEstado(datos) {
    if (!datos) {
      return;
    }
    if (datos.servicio_id) {
      servicio.value = String(datos.servicio_id);
    }
    cambiarServicio();
    if (datos.tipo_conjunto_id) {
      tipo.value = String(datos.tipo_conjunto_id);
      cambiarTipo();
    }
    if (datos.ficha_id) {
      ficha.value = String(datos.ficha_id);
      cambiarFicha();
    }
    if (typeof datos.quien_entrega === 'string') {
      quien.value = datos.quien_entrega;
    }
    if (obs && typeof datos.obs === 'string') {
      obs.value = datos.obs;
    }
    actualizar();
  }

  function faltaPuesto() {
    return !!document.querySelector('.puesto-card.is-empty');
  }

  function avisarPuesto() {
    const aviso = document.querySelector('.puesto-card.is-empty');
    if (!aviso) {
      return;
    }
    aviso.scrollIntoView({ block: 'center', behavior: 'smooth' });
    const lista = aviso.querySelector('.sigte-lista-boton') || aviso.querySelector('select');
    if (lista) {
      lista.focus();
      if (lista.classList.contains('sigte-lista-boton')) {
        lista.click();
      }
    }
  }

  servicio.addEventListener('change', function () {
    cambiarServicio();
    guardarBorrador();
  });
  tipo.addEventListener('change', function () {
    cambiarTipo();
    guardarBorrador();
  });
  ficha.addEventListener('change', function () {
    cambiarFicha();
    guardarBorrador();
  });
  quien.addEventListener('input', function () {
    actualizar();
    guardarBorrador();
  });
  if (obs) {
    obs.addEventListener('input', guardarBorrador);
  }

  form.addEventListener('submit', function (evento) {
    guardarBorrador();
    if (!faltaPuesto()) {
      return;
    }
    evento.preventDefault();
    avisarPuesto();
  });

  if (document.querySelector('.recv-notificacion-exito')) {
    sessionStorage.removeItem(CLAVE_BORRADOR);
  }

  cambiarServicio();
  if (antiguo.tipo || antiguo.ficha || quien.value || (obs && obs.value)) {
    if (antiguo.tipo) {
      tipo.value = String(antiguo.tipo);
      cambiarTipo();
    }
    if (antiguo.ficha) {
      ficha.value = String(antiguo.ficha);
      cambiarFicha();
    }
  } else {
    aplicarEstado(leerBorrador());
  }
  actualizar();
})();
