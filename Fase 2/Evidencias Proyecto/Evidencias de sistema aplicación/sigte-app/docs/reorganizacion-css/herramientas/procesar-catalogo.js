// Limpia y divide catalogo.css: quita selectores muertos, declaraciones pisadas y reparte por vista.
const fs = require('fs');
const path = require('path');
const [, , origen, destino] = process.argv;
const css = fs.readFileSync(origen, 'utf8');
const mapa = new Map();
for (const l of fs.readFileSync(path.join(__dirname, 'mapa-catalogo.txt'), 'utf8').split('\n')) {
  const m = l.match(/^\s*(\d+) (\S+)\s+(?:\[([^\]]*)\] )?(.*)$/);
  if (m) mapa.set((m[3] || '').replace(/\s+/g, ' ') + '|' + m[4].replace(/\s+/g, ' '), m[2]);
}

function bloques(texto) {
  const res = []; let i = 0;
  while (i < texto.length) {
    if (/\s/.test(texto[i])) { i++; continue; }
    if (texto.startsWith('/*', i)) { const f = texto.indexOf('*/', i) + 2; res.push({ tipo: 'comentario', texto: texto.slice(i, f) }); i = f; continue; }
    const llave = texto.indexOf('{', i);
    const cabecera = texto.slice(i, llave).trim();
    let prof = 0, j = llave;
    for (; j < texto.length; j++) { if (texto[j] === '{') prof++; else if (texto[j] === '}') { prof--; if (prof === 0) break; } }
    res.push({ tipo: cabecera.startsWith('@') ? 'at' : 'regla', cabecera, cuerpo: texto.slice(llave + 1, j) });
    i = j + 1;
  }
  return res;
}
function partir(s, sep) {
  const out = []; let prof = 0, act = '', comilla = null;
  for (const ch of s) {
    if (comilla) { if (ch === comilla) comilla = null; act += ch; continue; }
    if (ch === '"' || ch === "'") comilla = ch;
    if (ch === '(' || ch === '[') prof++;
    if (ch === ')' || ch === ']') prof--;
    if (ch === sep && prof === 0) { out.push(act.trim()); act = ''; } else act += ch;
  }
  if (act.trim()) out.push(act.trim());
  return out;
}

// Lista plana de reglas en orden, con su media y su comentario de sección.
const reglas = [];
let seccion = null;
function recorrer(lista, media) {
  for (const b of lista) {
    if (b.tipo === 'comentario') { seccion = b.texto; continue; }
    if (b.tipo === 'at') { recorrer(bloques(b.cuerpo), b.cabecera.replace(/\s+/g, ' ')); continue; }
    const decl = partir(b.cuerpo, ';').map(d => {
      const k = d.indexOf(':');
      let valor = d.slice(k + 1).trim(); const imp = /!important\s*$/.test(valor);
      if (imp) valor = valor.replace(/\s*!important\s*$/, '');
      return { prop: d.slice(0, k).trim().toLowerCase(), valor, imp };
    });
    reglas.push({ media, selectores: partir(b.cabecera, ',').map(s => s.replace(/\s+/g, ' ')), decl, seccion });
    seccion = null;
  }
}
recorrer(bloques(css), '');

const vistaDe = (media, s) => {
  const v = mapa.get(media + '|' + s);
  if (!v) throw new Error('Selector sin clasificar: ' + media + ' ' + s);
  return v;
};
const informe = { muertos: [], pisadas: [] };

// 1. Selectores que no aparecen en ninguna vista.
for (const r of reglas) {
  const vivos = r.selectores.filter(s => vistaDe(r.media, s) !== 'NINGUNA');
  if (vivos.length !== r.selectores.length) informe.muertos.push(...r.selectores.filter(s => !vivos.includes(s)));
  r.selectores = vivos;
}

// 2. Declaraciones que una regla posterior con el mismo selector siempre pisa.
const CUBRE = {
  padding: /^padding-(top|right|bottom|left)$/, margin: /^margin-(top|right|bottom|left)$/,
  background: /^background-/, border: /^border-(top|right|bottom|left)(-(width|style|color))?$|^border-(width|style|color)$/,
  'border-radius': /^border-(top|bottom)-(left|right)-radius$/, gap: /^(row|column)-gap$/,
  flex: /^flex-(grow|shrink|basis)$/, inset: /^(top|right|bottom|left)$/, overflow: /^overflow-(x|y)$/,
  font: /^font-(size|weight|style|family|variant)$|^line-height$/,
};
const pisa = (posterior, anterior) => posterior === anterior || (CUBRE[posterior] && CUBRE[posterior].test(anterior));
for (let i = 0; i < reglas.length; i++) {
  const r = reglas[i];
  if (!r.selectores.length) continue;
  r.decl = r.decl.filter(d => {
    const cubierta = r.selectores.every(s => reglas.slice(i + 1).some(o =>
      (o.media === r.media || o.media === '') && o.selectores.includes(s) &&
      o.decl.some(od => pisa(od.prop, d.prop) && (od.imp || !d.imp))));
    if (cubierta) informe.pisadas.push(`${r.selectores.join(', ')}${r.media ? ' [' + r.media + ']' : ''} → ${d.prop}`);
    return !cubierta;
  });
}

// 3. Reparto por vista conservando el orden original.
const archivoDe = v => (['consulta', 'admin', 'ficha'].includes(v) ? v : 'comun');
const salida = { comun: [], consulta: [], admin: [], ficha: [] };
const ultimaSeccion = {};
let seccionActual = null;
for (const r of reglas) {
  if (r.seccion && r.seccion !== '/* catalogo */') seccionActual = r.seccion;
  if (!r.selectores.length || !r.decl.length) continue;
  const grupos = {};
  for (const s of r.selectores) (grupos[archivoDe(vistaDe(r.media, s))] ||= []).push(s);
  for (const [k, selectores] of Object.entries(grupos)) {
    const lista = salida[k];
    const cuerpo = (sangria) => `${selectores.map(s => sangria + s).join(',\n')} {\n${r.decl.map(d => `${sangria}    ${d.prop}: ${d.valor}${d.imp ? ' !important' : ''};`).join('\n')}\n${sangria}}`;
    if (seccionActual && ultimaSeccion[k] !== seccionActual) { lista.push({ comentario: seccionActual }); ultimaSeccion[k] = seccionActual; }
    const previo = lista[lista.length - 1];
    if (r.media && previo && previo.media === r.media) previo.reglas.push(cuerpo('    '));
    else if (r.media) lista.push({ media: r.media, reglas: [cuerpo('    ')] });
    else lista.push({ texto: cuerpo('') });
  }
}
const CABECERAS = {
  comun: '/* Catálogo: piezas compartidas por consulta, administración y ficha (buscador, tarjetas, estructura de ficha). */',
  consulta: '/* Catálogo de consulta (operador y enfermera). Se carga después de comun.css. */',
  admin: '/* Administrar catálogo: fichas, instrumentos y formularios de alta. Se carga después de comun.css. */',
  ficha: '/* Ficha de consulta del operador y checklist de revisión. Se carga después de comun.css. */',
};
fs.mkdirSync(destino, { recursive: true });
for (const [k, items] of Object.entries(salida)) {
  const texto = items.map(it => it.comentario ? '\n' + it.comentario : it.media ? `${it.media} {\n${it.reglas.join('\n')}\n}` : it.texto).join('\n');
  fs.writeFileSync(path.join(destino, k + '.css'), CABECERAS[k] + '\n' + texto.replace(/^\n+/, '') + '\n');
  console.log(k.padEnd(9), items.filter(i => !i.comentario).length, 'bloques', texto.split('\n').length, 'líneas');
}
fs.writeFileSync(path.join(__dirname, 'informe-catalogo.txt'), `MUERTOS (${informe.muertos.length})\n${informe.muertos.join('\n')}\n\nPISADAS (${informe.pisadas.length})\n${informe.pisadas.join('\n')}\n`);
console.log('muertos', informe.muertos.length, 'pisadas', informe.pisadas.length);
