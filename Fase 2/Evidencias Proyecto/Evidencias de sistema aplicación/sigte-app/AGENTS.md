# Instrucciones para agentes de IA — SIGTE

SIGTE es una app Laravel 11 (Blade, PostgreSQL, Docker) para la trazabilidad de cajas de esterilización del Hospital San José de Melipilla.

## Reglas de la usuaria

- Responder siempre en español.
- No hacer commit ni push: ella revisa y sube los cambios.
- Nunca subir el `.env` local a producción.
- No hacer `git pull` en el VPS mientras su git esté desordenado, y nunca sin respaldo antes.
- Antes de tocar algo grande, mostrar opciones; le gusta verlas visualmente y elegir.
- El navegador integrado no deja escribir contraseñas: no se puede entrar con ningún rol. Las pruebas con sesión real se le piden a ella.
- Cualquier render o exportación temporal con datos reales se borra al terminar.

## Entorno

- Contenedores `sigte-app` (`/var/www/html`) y `sigte-db`; app en `http://localhost:8080`.
- Tras cambiar Blade: `docker exec sigte-app php artisan view:clear`.
- El repositorio git tiene su raíz en `C:\Users\yamil\Documents\SIGTE`; esta app es una subcarpeta.

## Trabajo en curso: reorganización de CSS y JS

Se está ordenando el CSS (un CSS general y un CSS por módulo o vista) y sacando el JS de las vistas Blade, módulo por módulo y verificando que nada cambie visualmente. Fases terminadas: CSS general, Reportes y Catálogo. **Sigue Flujo.**

Antes de tocar CSS o JS, leer `docs/reorganizacion-css/README.md`. Ahí está lo hecho, las convenciones, el método de verificación con sus herramientas, los errores conocidos y el plan paso a paso de la fase Flujo.
