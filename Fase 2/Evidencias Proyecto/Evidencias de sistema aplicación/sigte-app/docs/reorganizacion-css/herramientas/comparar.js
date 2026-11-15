// Compara dos mediciones por página, ignorando <script> (cambian al extraer JS).
const fs = require('fs');
const path = require('path');
const [, , a, b] = process.argv;
const out = path.join(__dirname, 'out');
function cargar(etiqueta, v) {
  const paginas = new Map();
  for (const l of fs.readFileSync(path.join(out, `${etiqueta}-${v}.txt`), 'utf8').split('\n')) {
    const [pag, , sig, h] = l.split('|');
    if (!sig || sig.startsWith('script')) continue;
    if (!paginas.has(pag)) paginas.set(pag, []);
    paginas.get(pag).push([sig, h]);
  }
  return paginas;
}
for (const v of ['claro-1280', 'oscuro-1280', 'claro-390']) {
  const A = cargar(a, v), B = cargar(b, v);
  const grupos = new Map(); const primeros = new Map(); let total = 0;
  for (const [pag, xs] of A) {
    const ys = B.get(pag) || [];
    if (xs.length !== ys.length) { console.log(`  ${pag}: ${xs.length} vs ${ys.length} elementos`); }
    for (let i = 0; i < Math.max(xs.length, ys.length); i++) {
      const x = xs[i] || [], y = ys[i] || [];
      if (x[0] === y[0] && x[1] === y[1]) continue;
      total++;
      const k = x[0] === y[0] ? `${pag} ${x[0]}` : `DESALINEADO ${pag} ${x[0]} <> ${y[0]}`;
      grupos.set(k, (grupos.get(k) || 0) + 1);
      if (!primeros.has(k)) primeros.set(k, i);
    }
  }
  console.log(`== ${v}: ${total} distintos`);
  [...grupos].sort((p, q) => q[1] - p[1]).slice(0, 25).forEach(([k, n]) => console.log(`  ${String(n).padStart(5)}x ${k} (pos ${primeros.get(k)})`));
}
