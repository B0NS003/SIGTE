// Clasifica cada selector de catalogo.css según las vistas donde aparecen sus clases e ids.
const fs = require('fs');
const path = require('path');
const raiz = process.argv[2];
const leer = p => fs.readFileSync(path.join(raiz, p), 'utf8');

const comunes = ['resources/views/layouts/app.blade.php', 'public/js/sigte-controles.js', 'public/js/sigte-tema.js']
  .concat(fs.readdirSync(path.join(raiz, 'resources/views/partials')).map(f => 'resources/views/partials/' + f))
  .concat(fs.readdirSync(path.join(raiz, 'resources/views/layouts/partials')).map(f => 'resources/views/layouts/partials/' + f))
  .map(leer).join('\n');
const vistas = {
  consulta: leer('resources/views/catalogo/index.blade.php'),
  admin: leer('resources/views/catalogo/admin.blade.php'),
  ficha: leer('resources/views/catalogo/ficha.blade.php'),
};
const palabras = t => new Set(t.match(/[A-Za-z][\w-]*/g));
const enComun = palabras(comunes);
const enVista = Object.fromEntries(Object.entries(vistas).map(([k, t]) => [k, palabras(t)]));

function bloques(texto) {
  const res = []; let i = 0;
  while (i < texto.length) {
    if (/\s/.test(texto[i])) { i++; continue; }
    if (texto.startsWith('/*', i)) { const f = texto.indexOf('*/', i) + 2; res.push({ tipo: 'comentario', texto: texto.slice(i, f), linea: texto.slice(0, i).split('\n').length }); i = f; continue; }
    const llave = texto.indexOf('{', i);
    const cabecera = texto.slice(i, llave).trim();
    let prof = 0, j = llave;
    for (; j < texto.length; j++) { if (texto[j] === '{') prof++; else if (texto[j] === '}') { prof--; if (prof === 0) break; } }
    res.push({ tipo: cabecera.startsWith('@') ? 'at' : 'regla', cabecera, cuerpo: texto.slice(llave + 1, j), inicio: i });
    i = j + 1;
  }
  return res;
}
function partir(s) {
  const out = []; let prof = 0, act = '';
  for (const ch of s) {
    if (ch === '(' || ch === '[') prof++;
    if (ch === ')' || ch === ']') prof--;
    if (ch === ',' && prof === 0) { out.push(act.trim()); act = ''; } else act += ch;
  }
  if (act.trim()) out.push(act.trim());
  return out;
}
function vistasDe(selector) {
  const nombres = [...selector.replace(/\[[^\]]*\]/g, '').matchAll(/[.#]([a-zA-Z][\w-]*)/g)].map(m => m[1]);
  const propios = nombres.filter(n => !enComun.has(n));
  if (!nombres.length) return ['global'];
  const r = Object.keys(vistas).filter(v => nombres.every(n => enComun.has(n) || enVista[v].has(n)));
  if (!propios.length && r.length === 3) return ['global'];
  return r;
}

const css = fs.readFileSync(path.join(raiz, 'public/css/catalogo.css'), 'utf8');
const linea = pos => css.slice(0, pos).split('\n').length;
const filas = [];
function recorrer(lista, base, media) {
  for (const b of lista) {
    if (b.tipo === 'at') {
      if (/^@(media|supports)/.test(b.cabecera)) recorrer(bloques(b.cuerpo), base + b.inicio + css.slice(base + b.inicio).indexOf('{') + 1, b.cabecera);
      else filas.push({ linea: linea(base + b.inicio), sel: b.cabecera, vistas: ['at'], media });
      continue;
    }
    if (b.tipo !== 'regla') continue;
    for (const s of partir(b.cabecera)) filas.push({ linea: linea(base + b.inicio), sel: s, vistas: vistasDe(s), media });
  }
}
recorrer(bloques(css), 0, '');
const resumen = {};
for (const f of filas) { const k = f.vistas.join('+') || 'NINGUNA'; resumen[k] = (resumen[k] || 0) + 1; }
console.log(resumen);
fs.writeFileSync(path.join(__dirname, 'mapa-catalogo.txt'), filas.map(f => `${String(f.linea).padStart(5)} ${(f.vistas.join('+') || 'NINGUNA').padEnd(22)} ${f.media ? '[' + f.media + '] ' : ''}${f.sel}`).join('\n'));
