# Reorganización de CSS y JS de SIGTE — traspaso para la próxima sesión

Última actualización: 11/11/2026. Fases 1 a 5 terminadas y verificadas (CSS base/componentes + reportes + catálogo + flujo CSS/JS + módulos restantes). Pendientes opcionales en sección 8.

## 1. Contexto y reglas de trabajo

SIGTE es una app Laravel 11 (Blade, PostgreSQL, Docker) para la trazabilidad de cajas de esterilización del Hospital San José de Melipilla.

Reglas de la usuaria (obligatorias):

- Responder siempre en español.
- No hacer commit ni push: ella revisa y sube los cambios.
- Nunca subir el `.env` local a producción.
- No hacer `git pull` en el VPS mientras su git esté desordenado, y nunca sin respaldo antes.
- Antes de tocar algo grande, mostrar opciones; le gusta verlas visualmente y elegir.
- El navegador integrado no deja escribir contraseñas: no se puede entrar como administradora, secretaria, enfermera u operador. Las pruebas con sesión real (guardar, avanzar etapas, etc.) se le piden a ella.
- Los renders temporales contienen datos reales: se borran al terminar.

Entorno:

- Contenedores `sigte-app` (código en `/var/www/html`) y `sigte-db`. La app corre en `http://localhost:8080`.
- Tras cambiar Blade: `docker exec sigte-app php artisan view:clear`. Si algo sigue en caché: `docker exec sigte-app apache2ctl -k graceful`.
- El CSS y el JS se versionan con `?v={{ @filemtime(public_path(...)) }}`.
- Modo oscuro: `html[data-tema="oscuro"]`, guardado en `localStorage` con la clave `sigte-tema`.
- El repositorio git tiene su raíz en `C:\Users\yamil\Documents\SIGTE` (remoto `origin`, GitHub `B0NS003/SIGTE`); la app está en una subcarpeta. `git diff` no muestra si algo cambió visualmente: para eso está la comparación de estilos computados (sección 4).
- Node 24 disponible en Windows; PHP 8.3 dentro del contenedor; Microsoft Edge para el modo headless.

## 2. Objetivo y estructura

La usuaria eligió la opción A: estructura completa sin cambiar el despliegue (sin Vite ni build), módulo por módulo, verificando que nada cambie visualmente.

```
public/css/
  base/            tokens.css, base.css, layout.css, utilidades.css
  componentes/     botones, formularios, pestanas, paneles, tablas, insignias, mensajes
  modulos/
    reportes/      comun.css, admin.css, secretaria.css
    catalogo/      comun.css, consulta.css, admin.css, ficha.css
    flujo/         comun.css, panel.css, recepcion.css, entrega.css
    login/         login.css
    enfermera/     comun.css, panel.css, cierre.css
    secretaria/    panel.css
    inventario/    inventario.css
    admin/         comun.css, usuarios.css, turnos.css, historial.css, custodia.css, alertas.css
  inventario/      pestanas, tarjeta-prioridad, registro-movimiento, configurar-minimo, modal-reposicion
public/js/
  modulos/reportes/admin.js
  modulos/catalogo/admin.js, ficha.js
  modulos/flujo/   track-anim.js, proceso-reloj.js, modales.js, entrega.js, recepcion.js
resources/views/layouts/partials/estilos-base.blade.php   (carga base + componentes en orden)
```

Convenciones:

- El layout incluye `estilos-base` y después `@stack('styles')`. Cada vista hace `@push('styles')` solo con lo suyo: primero `modulos/<modulo>/comun.css` y luego el archivo de su vista.
- Cada archivo empieza con un comentario de una línea que dice qué contiene y qué se carga antes.
- Los valores que vienen de datos (anchos y altos de barras, colores por servicio) se pasan como variables CSS en el HTML (`style="--progreso-ancho: 40%"`) y el diseño vive en el CSS.
- Los datos que el JS necesita desde Blade van en `<script type="application/json" id="...">@json($variable)</script>` y el JS hace `JSON.parse(document.getElementById(...).textContent)`.
- Los comentarios de código se escriben solo para restricciones que el código no muestra.

## 3. Lo que se hizo

### Fase 1 — CSS general (`sigte.css` borrado)

- `sigte.css` se dividió en `base/` y `componentes/` (11 archivos) y se cargan desde `estilos-base.blade.php`.
- Las reglas que solo usaba un módulo se movieron al **principio** del CSS de ese módulo, para conservar la cascada: `login.css`, `flujo.css`, `enfermera.css`, `secretaria.css`, `admin.css`, `inventario.css` y `catalogo.css`.
- Se borró código muerto (`queda-*`, `dash-*`, `mini-*`, `bar-*`, `sala-tab*`, etc.).
- Se creó el token `--sidebar-texto` (los enlaces del sidebar en modo oscuro casi no se veían).
- `public/guia-visual.html` y `public/guia-tipografia.html` apuntan a los archivos nuevos.

### Fase 2 — Reportes (`reportes.css` borrado)

- Dividido en `modulos/reportes/comun.css`, `admin.css` y `secretaria.css`.
- Se quitaron 6 clases muertas y reglas anuladas, se fusionaron 5 selectores duplicados y se quitaron 2 `!important` subiendo la especificidad.
- Los `style=""` de los gráficos ahora pasan solo variables CSS.
- 6 "degradados" de un solo color pasaron a ser colores sólidos (verificado: mismo token).
- El `<script>` de pestañas y filtros pasó a `public/js/modulos/reportes/admin.js`.

### Fase 3 — Catálogo (`catalogo.css` borrado)

- Dividido en `modulos/catalogo/comun.css`, `consulta.css` (vista `catalogo/index`), `admin.css` (`catalogo/admin`) y `ficha.css` (`catalogo/ficha`).
- Se quitaron 9 selectores muertos y 55 declaraciones pisadas; quedan 4 `!important`, todos del patrón `[hidden]`.
- El JS de `catalogo/admin.blade.php` (unas 745 líneas) pasó a `public/js/modulos/catalogo/admin.js`.
  - Los datos van en el bloque JSON `#catalogo-admin-datos`, armado en un `@php`.
  - Se borraron 2 funciones que nadie llamaba (`htmlCard`, `escribirCard`).
- El checklist de la ficha pasó a `public/js/modulos/catalogo/ficha.js`.
- Verificado en 10 estados de página × 3 modos: 0 diferencias.
- Pendiente que la usuaria pruebe con sesión: guardar ficha y guardar instrumento.

## 4. Método de verificación (cómo se garantiza que nada cambie)

La idea: renderizar las páginas reales como cada rol, medir en Edge headless los estilos computados de **todos** los elementos del `body` en 3 variantes (claro 1280 px, oscuro 1280 px, claro 390 px), y comparar antes contra después elemento por elemento.

Herramientas en `docs/reorganizacion-css/herramientas/`. Copiarlas antes de usarlas, porque los scripts buscan `%TEMP%\sigte-ref`:

```powershell
$t = Join-Path $env:TEMP 'sigte-ref'; New-Item -ItemType Directory -Force $t, "$t\out" | Out-Null
Copy-Item 'docs\reorganizacion-css\herramientas\*' $t
docker cp "$t\render.php" sigte-app:/tmp/render.php
Copy-Item "$t\harness.html" 'public\_ref\harness.html'   # crear public\_ref antes si no existe
```

| Herramienta | Qué hace |
|---|---|
| `render.php` | `php /tmp/render.php <carpeta> <css-alt o -> [paginas,separadas,por,coma]`. Inicia sesión con el primer usuario de cada rol, renderiza y guarda en `public/_ref/<carpeta>/`. Usar `-` como segundo argumento (PowerShell pierde los argumentos vacíos `''`). Las páginas `avanzar`, `actividad` y `reportes` salen como redirección (302): se borran. |
| `harness.html` | Página que carga cada render en un iframe, desactiva animaciones y transiciones y emite `pagina\|indice\|etiqueta\|hash` por elemento. Parámetros: `d`, `tema`, `w`, `p` (lista de páginas) y `solo` (`pagina:indice` para volcar todas las propiedades). Una página puede llevar `@accion` (por ejemplo `catalogo-admin@lista`) y la acción se ejecuta antes de medir: ver el objeto `ACCIONES`. |
| `medir.ps1` | `-Dir <carpeta> -Etiqueta <nombre> [-Paginas lista] [-Solo pagina:idx,...] [-Variantes claro-1280,...]`. Escribe `out\<etiqueta>-<variante>.txt`. |
| `comparar.js` | `node comparar.js <etiquetaA> <etiquetaB>`. Compara por página, **ignorando los `<script>`** (al extraer JS cambia su cantidad y se desplazan los índices). |
| `mapa-catalogo.js` | Clasifica cada selector según en qué vista aparecen sus clases e ids. Escribe `mapa-catalogo.txt`; `NINGUNA` = candidato a código muerto. |
| `procesar-catalogo.js` | Divide el CSS por vista usando el mapa (ver sección 5). |
| `extraer-js-catalogo.js` | Ejemplo de extracción de un `<script>` con `@json` a un archivo JS más un bloque JSON. |
| `verificar-catalogo.ps1` | Ejemplo de ciclo completo: `view:clear`, render, medición y comparación. |

Ciclo de cada fase:

1. **Referencia:** renderizar en `antes` y medir **dos veces** (`<mod>-antes` y `<mod>-ruido`). La diferencia entre ambas es el ruido normal: reloj del operador (`strong.proceso-texto`), loader animado `sigte-carga-*` y la hora del pie en `panel-admin`.
2. **Acciones:** comprobar que cada `@accion` cambia la página (comparando contra la página sin acción). Si da 0, la acción no hace nada.
3. Hacer **un** tipo de cambio a la vez: primero dividir el CSS, verificar; luego extraer el JS, verificar; luego `!important` y duplicados, verificar.
4. Ante una diferencia, buscar el primer índice afectado y medir con `-Solo` en `antes` y `despues`. Comparar propiedad por propiedad y buscar en el mapa qué reglas tocan ese elemento.
5. Probar a mano en el navegador integrado (`http://localhost:8080/_ref/despues/<pagina>.html`) las interacciones que el arnés no cubre.
6. Al terminar, borrar `public\_ref` y `docker exec sigte-app rm -f /tmp/render.php` (comandos separados y con ruta absoluta; ver sección 7).

Tiempos: una medición de las 3 variantes de catálogo (164 mil elementos) tarda unos 5 minutos. Usar `block_until_ms` alto y `-Paginas` para medir solo lo necesario.

## 5. Reglas técnicas aprendidas (evitan errores conocidos)

- **Orden de carga y cascada:** separar en archivos cambia el orden relativo entre reglas compartidas y las de cada vista. Si una regla compartida estaba después de una de vista con la misma especificidad, al pasar a `comun.css` (que carga antes) **pierde**. Por eso:
  - una lista de selectores mixta (`#tab-catalogo .x, .cat-area .x { }`) se **parte** y cada selector va a su archivo;
  - lo que se mueve fuera del CSS general va al **principio** del archivo del módulo.
- **Declaraciones pisadas (eliminación segura):** una propiedad P del selector S está muerta si más abajo el **mismo** S (en el mismo `@media`, o sin `@media`) vuelve a declarar P, o un shorthand que la cubra, con importancia igual o mayor. Borrarla nunca cambia el resultado. Fusionar reglas, en cambio, **sí** puede cambiarlo si entre medio hay otras reglas que tocan esas propiedades: revisar eso antes de fusionar.
- **Quitar `!important`:** buscar contra qué regla compite y subir la especificidad justa (por ejemplo `.reportes-tabla .reportes-vacio` o `.sp-lateral .sp-numero`). Revisar también los `!important` globales: `base/layout.css` y `componentes/formularios.css`. Dejar el patrón `[hidden] { display: none !important; }` hasta decidir una utilidad global.
- **`@json` con un arreglo literal no funciona:** Blade corta la expresión en la primera coma. Armar el arreglo en `@php $datos = [...]; @endphp` y luego `@json($datos)`. Los flags por defecto de `@json` (`JSON_HEX_TAG`, etc.) hacen seguro el bloque `<script type="application/json">`.
- **Orden de ejecución del JS:** si el `<script>` estaba en medio del contenido, el `<script src>` va en el mismo lugar. Si estaba en `@once @push('scripts')`, se mantiene ese `@push` y solo cambia el contenido por `src`. Las funciones globales que usan otros scripts o el HTML (por ejemplo `mostrarTab`) siguen globales en un script clásico (no módulo).
- **PowerShell:**
  - escribir archivos con `[IO.File]::WriteAllText(ruta, texto, (New-Object Text.UTF8Encoding $false))`, sin BOM;
  - `\"` no escapa comillas dentro de comillas dobles;
  - `@filemtime` dentro de comillas dobles rompe el comando (mejor editar con la herramienta de reemplazo);
  - las rutas relativas en `[IO.File]` se resuelven desde otra carpeta, así que hay que usar rutas absolutas.
- **Auto-review** del entorno: a veces bloquea borrados recursivos encadenados (hacerlos uno por uno con `-LiteralPath` absoluto) o scripts en `%TEMP%`. Si el bloqueo es injustificado, reintentar el mismo comando pidiendo aprobación.
- Las comparaciones grandes en PowerShell con `Group-Object` son lentísimas: usar `comparar.js`.

## 6. Fase 4 — Flujo

### 6.0 Hecho el 11/11 (CSS y JS)

`flujo.css` se dividió y se borró. Las vistas cargan `modulos/flujo/comun.css` y después el archivo de su pantalla (`panel`, `recepcion` o `entrega`). `avanzar` y `actividad` cargan `comun.css` y `panel.css`; no se borraron.

JS extraído a `public/js/modulos/flujo/`:

| Archivo | Origen |
|---|---|
| `track-anim.js` | `partials/track-anim` (después de `anime.min.js`) |
| `proceso-reloj.js` | `partials/proceso-reloj` |
| `modales.js` | `partials/modales-flujo` (los JSON `#pasos-etapa` y `#anotaciones-caja` siguen en el Blade) |
| `entrega.js` | `entrega.blade.php` |
| `recepcion.js` | `recepcion.blade.php` (`old()` pasó a `#recv-datos-old`) |

- En esta máquina no hay Node. El mapa, el procesado y la comparación se hicieron en PHP: `mapa-flujo.php`, `procesar-flujo.php` y `comparar.php`. `medir.ps1` sigue usando Edge.
- El mapa dejó 48 selectores muertos y 5 declaraciones pisadas. `ops-etapa--*` se arma en Blade y quedó en `panel.css`.
- Verificación CSS: 5 páginas × 3 variantes, ruido esperado (reloj, pulso, loader).
- Verificación JS: panel + recepción + entrega × 3 variantes. Solo ruido (`track-node`, `badge-listo`, `proceso-texto`, loader). Recepción y entrega sin diferencias propias.
- Pendiente que la usuaria pruebe con sesión: abrir modales, anotar, pasar etapa, registrar recepción y entrega.
- `actividad.blade.php` todavía tiene un `<script>` chico con `@json`; la vista parece muerta.

### 6.1 Inventario actual

- `public/css/flujo.css`: 3.020 líneas, 12 `!important`, 14 `@media`, 37 reglas de modo oscuro.
  - Las primeras ~240 líneas son reglas que se movieron ahí en la fase 1 (avance, entrega y modo oscuro del flujo).
  - Secciones (comentarios): Avance de etapa, Entrega, Modo oscuro, Operator tracking board (línea 240), contorno por etapa (552), dos por fila (702), lista compacta (1114), flujo interno (1309), Nueva recepción (1489), listo para pasar (2291), Recepción desde catálogo (2556), Entrega por servicio (2826).
  - **Codificación:** el comentario de la línea 2556 tiene 2 caracteres inválidos ("Recepci�n desde cat�logo"). Al regenerar el archivo, reescribir ese comentario como "Recepción desde catálogo".
- Vistas que cargan `flujo.css`:
  - `flujo/panel.blade.php`, el panel del operador. Su enlace termina en `?v=...-ops8`; ese sufijo puede quitarse, porque `filemtime` ya versiona.
  - `flujo/recepcion.blade.php` y `flujo/entrega.blade.php`.
  - `flujo/avanzar.blade.php` y `flujo/actividad.blade.php`: **parecen vistas muertas**. `FlujoController::avanzar()` y `::actividad()` solo redirigen a `mockups.panel?accion=etapa` y nadie hace `view('flujo.avanzar')` ni `view('flujo.actividad')`. **Preguntar a la usuaria antes de borrarlas.** Si se borran, sus clases exclusivas quedan como código muerto en el mapa.
- Partials:
  - `modales-flujo.blade.php`: 575 líneas de JS dentro de `@once @push('scripts')`. Ya usa bloques JSON (`#pasos-etapa`, `#anotaciones-caja`) y lee el CSRF desde `meta[name="csrf-token"]`, así que no tiene Blade dentro del JS.
  - `proceso-reloj.blade.php`: 161 líneas de JS en `@once @push`, sin Blade.
  - `track-anim.blade.php`: carga `js/anime.min.js` más 55 líneas de JS en `@once @push`, sin Blade. Se incluye desde `tarjeta-caja` dentro de un bucle; gracias a `@once` sale una sola vez.
  - `tarjeta-caja.blade.php` y `puesto.blade.php`: sin JS. `puesto` lo usan panel, recepción y entrega.
- JS en línea en vistas:
  - `entrega.blade.php`: 104 líneas, sin Blade.
  - `recepcion.blade.php`: 58 líneas. Ya tiene bloques JSON (`#recv-datos-tipos`, `#recv-datos-fichas`), pero la línea 68 usa `@json(old('tipo_conjunto_id'))` y `@json(old('ficha_id'))`. Pasarlos a otro bloque JSON o a `data-*`.
  - `actividad.blade.php`: 22 líneas con un `@json` (vista probablemente muerta).
- Atributos `style=""`: recepción 2, entrega 1, avanzar 3 y actividad 2. Si son datos, convertirlos en variables CSS.

### 6.2 Estructura propuesta (confirmarla con el mapa)

```
public/css/modulos/flujo/
  comun.css       puesto, track/etapas y piezas que usan panel, recepción y entrega
  panel.css       tablero del operador, tarjetas de caja, modales de etapa/actividad, reloj
  recepcion.css   nueva recepción y recepción desde catálogo
  entrega.css     entrega por servicio
public/js/modulos/flujo/
  modales.js, proceso-reloj.js, track-anim.js, recepcion.js, entrega.js
```

Si el mapa muestra que los modales son un bloque grande y aislado, se puede separar `modales.css`. Antes de decidir la división final, mostrar opciones a la usuaria.

### 6.3 Pasos

1. **Preparar las herramientas** (sección 4).
2. **Páginas a medir:** `panel-operador`, `panel-operador-etapa` (`?accion=etapa`), `panel-operador-actividad` (`?accion=actividad`), `recepcion` y `entrega`. Ya están en `render.php`. `avanzar` y `actividad` dan 302 y no se miden.
3. **Acciones del arnés:** leer el JS de `modales-flujo` y agregar en `ACCIONES` de `harness.html` una acción por estado relevante, por ejemplo:
   - abrir el modal de etapa;
   - abrir el modal de actividad o anotaciones;
   - expandir el acordeón de la lista compacta;
   - en recepción, elegir servicio, tipo y ficha para que aparezcan las opciones;
   - en entrega, el estado tras elegir un servicio.

   Comprobar que cada acción cambia la página (paso 2 del ciclo).
4. **Ruido:** medir la referencia dos veces. El reloj (`proceso-reloj`) y `anime.js` (`track-anim`) cambian estilos en línea con el tiempo. Si el ruido es grande, agregar en el arnés un stub antes de cargar (por ejemplo, neutralizar `window.anime`) o ignorar esos elementos en `comparar.js`, y documentarlo.
5. **Mapa:** adaptar `mapa-catalogo.js` como `mapa-flujo.js`:
   - origen `public/css/flujo.css`;
   - texto de cada vista = la vista + sus partials + el JS que generará clases;
   - vista panel = `flujo/panel` + `modales-flujo`, `tarjeta-caja`, `puesto`, `proceso-reloj` y `track-anim`;
   - recepción = `recepcion` + `puesto`;
   - entrega = `entrega` + `puesto`;
   - textos comunes = layout, `partials/`, `layouts/partials/`, `sigte-controles.js` y `sigte-tema.js`.

   Revisar a mano los `NINGUNA`: buscar si la clase se arma concatenando texto en JS (`'flujo-' + x`, plantillas `${}`) antes de darla por muerta. Las clases de etapa del tipo `*-etapa-N` suelen armarse así.
6. **Procesar:** adaptar `procesar-catalogo.js` (vistas `panel`, `recepcion` y `entrega`; todo lo compartido a `comun`). Ya trae:
   - el borrado de declaraciones pisadas;
   - la separación de listas mixtas;
   - la unión de `@media` contiguos;
   - el formato normalizado y los comentarios de sección por archivo.

   Revisar el informe (`informe-*.txt`) y los archivos generados antes de instalarlos.
7. **Instalar** en `public/css/modulos/flujo/` y actualizar los `@push('styles')` de las vistas. Verificar **solo el CSS**. Esperado: 0 diferencias fuera del ruido.
8. **Extraer el JS**, una pieza por vez, verificando entre cada una:
   - `track-anim`: el `<script src>` de `anime.min.js` debe quedar **antes** de `track-anim.js`;
   - `proceso-reloj`;
   - `modales`;
   - `entrega`;
   - `recepcion`, con su bloque JSON para `old()`.

   Mantener `@once @push('scripts')` donde ya existía y usar `node --check` en cada archivo.
9. **Limpieza final:** `!important` (12), duplicados restantes y `style=""` a variables. Verificar.
10. **Navegador:** sobre los renders, abrir modales, cambiar selects y expandir acordeones; revisar la consola. Lo que envía datos (avanzar etapa, guardar recepción o entrega, anotaciones) se lo pide probar a la usuaria con sesión real.
11. **Borrar** `public/css/flujo.css` cuando no quede ninguna referencia (`rg "flujo\.css"`), borrar los temporales y resumirle a la usuaria.

## 7. Limpieza de temporales (siempre)

```powershell
Remove-Item -LiteralPath 'C:\Users\yamil\Documents\SIGTE\Fase 2\Evidencias Proyecto\Evidencias de sistema aplicación\sigte-app\public\_ref' -Recurse -Force
docker exec sigte-app rm -f /tmp/render.php
```

Ejecutarlos como comandos separados. `public/_ref` tiene datos reales y nunca debe quedar en el proyecto.

## 8. Fase 5 — Módulos restantes (login, enfermera, secretaria, inventario, admin)

Hecho el 11/11/2026 con el mismo método (mapa PHP + procesar + medir/comparar). Herramientas nuevas: `mapa-modulo.php`, `procesar-modulo.php`.

| Módulo | Destino | Muertos quitados | Verificación |
|---|---|---|---|
| login | `modulos/login/login.css` | 2 (`.field-select` sin uso en login) | 0 diffs |
| enfermera | `comun` + `panel` + `cierre` | 5 (`.ct-*` legacy) | 0 diffs |
| secretaria | `modulos/secretaria/panel.css` | 51 (`prod-*` de UI antigua) | 0 diffs |
| inventario | `modulos/inventario/inventario.css` | 45 (libros/paso/reposicion viejos) | 0 diffs |
| admin | `comun` + usuarios/turnos/historial/custodia/alertas | 58 (alert-strip, nomina, rep-*, aud-relacionadas) | 0 diffs |

Notas:

- Secretaría producción/reportes ya no cargan `secretaria.css`; usan solo `modulos/reportes/*`. Insumos queda con base + componentes.
- El panel admin no tiene CSS exclusivo: carga `modulos/admin/comun.css` (shell móvil + piezas compartidas) y reportes.
- `public/css/inventario/*.css` (modales/pestañas) se dejó donde estaba; solo se movió el `inventario.css` raíz.
- Ya no existen en la raíz: `login.css`, `enfermera.css`, `secretaria.css`, `inventario.css`, `admin.css`.

Pendientes opcionales (proponer si se piden):

- Utilidad global `[hidden] { display: none !important; }` en `base/utilidades.css`.
- Unificar tokens antiguos (`--ink`, `--muted`, `--card`, `--borde`) con los nuevos.
- `catalogo/admin.js` usa `mensaje.style.color = '#b42318'`: pasarlo a una clase de error.
- Mover `public/css/inventario/` a `modulos/inventario/` si se quiere unificar rutas.

## 9. Observaciones encontradas (problemas previos, sin tocar)

- **Reportes > Entregas:** la tarjeta "Estado del período" se estira a la altura de la tabla de últimas entregas (unos 3.700 px con 81 filas) y su contenido queda centrado muy abajo.
- **Catálogo > + Nueva ficha, en móvil:** las reglas de `@media (max-width: 700px)` para `.mant-pieza` (columnas de la tabla de instrumentos) y para el alto de `.ficha-preview-photo` nunca se aplicaban, porque una regla posterior sin `@media` las anulaba; se eliminaron como declaraciones pisadas. En pantallas angostas la tabla no se reacomoda.
- Pendientes antiguos, solo si los pide: decisión de contraste del botón naranja principal y exportación Excel/PDF de Reportes (hoy los botones están deshabilitados y hay una ruta CSV sin enlazar).

## 10. Al subir cambios al VPS (para recordarle a la usuaria)

- Incluir las carpetas `public/css/base/`, `public/css/componentes/`, `public/css/modulos/` (login, enfermera, secretaria, inventario, admin, flujo, catalogo, reportes), `public/css/inventario/` (satélites) y `public/js/modulos/`, más `resources/views/layouts/partials/estilos-base.blade.php`.
- Ya no existen en la raíz: `sigte.css`, `reportes.css`, `catalogo.css`, `flujo.css`, `login.css`, `enfermera.css`, `secretaria.css`, `inventario.css`, `admin.css`.
- Ejecutar `php artisan view:clear` en el servidor.
- No subir `.env` ni `public/_ref`. La carpeta `docs/` es solo documentación.
