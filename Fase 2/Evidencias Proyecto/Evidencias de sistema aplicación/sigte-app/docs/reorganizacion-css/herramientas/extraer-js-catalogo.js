// Mueve el <script> de catalogo/admin.blade.php a public/js/modulos/catalogo/admin.js.
const fs = require('fs');
const path = require('path');
const raiz = process.argv[2];
const vista = path.join(raiz, 'resources/views/catalogo/admin.blade.php');
const texto = fs.readFileSync(vista, 'utf8');
const nl = texto.includes('\r\n') ? '\r\n' : '\n';
const inicio = texto.indexOf(nl + '<script>' + nl);
const fin = texto.indexOf('</script>' + nl + '@endsection');
if (inicio < 0 || fin < 0) throw new Error('No se encontró el bloque <script>');
let js = texto.slice(inicio + nl.length + '<script>'.length + nl.length, fin);

const reemplazos = [
  ['@json(collect($items)->keyBy(\'codigo\'))', 'datosCatalogo.fichas'],
  ['@json($servicios)', 'datosCatalogo.servicios'],
  ['@json($instrumentosCatalogo)', 'datosCatalogo.instrumentos'],
  ['@json(asset(\'images/catalogo/sin_imagen.png\'))', 'datosCatalogo.imagenRespaldo'],
  ['@json(route(\'mockups.catalogo.instrumentos.actualizar\', [\'instrumento\' => \'__ID__\']))', 'datosCatalogo.rutas.instrumentoActualizar'],
  ['@json(route(\'mockups.catalogo.instrumentos.guardar\'))', 'datosCatalogo.rutas.instrumentoGuardar'],
  ['@json($serviciosFicha)', 'datosCatalogo.serviciosFicha'],
  ['@json($tiposConjuntoInstrumento)', 'datosCatalogo.tiposFicha'],
  ['@json(route(\'mockups.catalogo.fichas.actualizar\', [\'ficha\' => \'__ID__\']))', 'datosCatalogo.rutas.fichaActualizar'],
  ['@json(route(\'mockups.catalogo.fichas.guardar\'))', 'datosCatalogo.rutas.fichaGuardar'],
  ['@json(route(\'mockups.catalogo.admin\'))', 'datosCatalogo.rutas.admin'],
];
for (const [de, a] of reemplazos) {
  if (!js.includes(de)) throw new Error('No aparece: ' + de);
  js = js.split(de).join(a);
}
if (/@json|\{\{|\{!!|@if|@foreach/.test(js)) throw new Error('Queda Blade en el script');
js = js.split(nl).map(l => l.startsWith('    ') ? l.slice(4) : l).join(nl);
js = '// Administrar catálogo: pestañas, filtros, instrumentos y alta/edición de fichas.' + nl +
  "var datosCatalogo = JSON.parse(document.getElementById('catalogo-admin-datos').textContent);" + nl + js;

const destino = path.join(raiz, 'public/js/modulos/catalogo/admin.js');
fs.mkdirSync(path.dirname(destino), { recursive: true });
fs.writeFileSync(destino, js.replace(/\s*$/, '') + nl);

const bloque = [
  '@php',
  '    $datosCatalogo = [',
  "        'fichas' => collect($items)->keyBy('codigo'),",
  "        'servicios' => $servicios,",
  "        'instrumentos' => $instrumentosCatalogo,",
  "        'imagenRespaldo' => asset('images/catalogo/sin_imagen.png'),",
  "        'serviciosFicha' => $serviciosFicha,",
  "        'tiposFicha' => $tiposConjuntoInstrumento,",
  "        'rutas' => [",
  "            'instrumentoGuardar' => route('mockups.catalogo.instrumentos.guardar'),",
  "            'instrumentoActualizar' => route('mockups.catalogo.instrumentos.actualizar', ['instrumento' => '__ID__']),",
  "            'fichaGuardar' => route('mockups.catalogo.fichas.guardar'),",
  "            'fichaActualizar' => route('mockups.catalogo.fichas.actualizar', ['ficha' => '__ID__']),",
  "            'admin' => route('mockups.catalogo.admin'),",
  '        ],',
  '    ];',
  '@endphp',
  '<script type="application/json" id="catalogo-admin-datos">@json($datosCatalogo)</script>',
  "<script src=\"{{ asset('js/modulos/catalogo/admin.js') }}?v={{ @filemtime(public_path('js/modulos/catalogo/admin.js')) }}\"></script>",
].join(nl);
const nuevo = texto.slice(0, inicio + nl.length) + bloque + nl + texto.slice(fin + '</script>'.length + nl.length);
fs.writeFileSync(vista, nuevo);
console.log('admin.js', js.split(nl).length, 'líneas; vista', nuevo.split(nl).length, 'líneas');
