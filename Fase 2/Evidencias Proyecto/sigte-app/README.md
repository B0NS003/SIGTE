# SIGTE — mockup preliminar (Fase 2)

> **Importante:** esto es un **mockup visual super preliminar** del Capstone.
> No hay lógica de negocio real, ni autenticación verdadera, ni base de datos operativa.
> Sirve como evidencia de avance de interfaz (Blade) para alinear roles, flujo de cajas y pantallas de gestión.

## Qué es

Prototipo frontend en **Laravel 11 + Blade** para el sistema de trazabilidad de esterilización del Hospital San José de Melipilla (SIGTE).

## Qué NO es (aún)

- No valida usuarios ni contraseñas
- No guarda datos en BD al confirmar formularios
- No implementa reglas de negocio completas (solo la idea visual)

## Cómo correrlo

```bash
cd "Fase 2/Evidencias Proyecto/sigte-app"
composer install
copy .env.example .env
php artisan key:generate
php artisan serve
```

Abrir: http://127.0.0.1:8000/login

## Equipo / Capstone

Proyecto académico Duoc UC — Capstone 2026.
Estado: **mockup preliminar de Fase 2 (evidencia de desarrollo)**.