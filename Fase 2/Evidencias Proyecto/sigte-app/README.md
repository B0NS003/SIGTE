# SIGTE — Laravel 11

Sistema Integral de Gestión y Trazabilidad de Esterilización  
Hospital San José de Melipilla

## Arranque local (cuando `vendor/` esté instalado)

```bash
cd C:\xampp\htdocs\sigte-app
php artisan key:generate
# crea database/database.sqlite si no existe
php artisan migrate --seed
php artisan serve
```

Abrir: http://127.0.0.1:8000/login

### Usuarios demo
| Email | Password | Rol |
|-------|----------|-----|
| tecnico@sigte.local | password | técnico |
| jefatura@sigte.local | password | jefatura |

## Stack
- Laravel 11 + PHP 8.2+
- Blade (vistas reales; ya no HTML suelto)
- Auth sesión + columna `role` (tecnico / jefatura; secretaria después)
- SQLite para empezar; PostgreSQL 16 vía Docker
- Feature test: login

## Docker (cuando tengas Docker Desktop)
```bash
docker compose up -d --build
```
App: http://localhost:8080

## Nota
La carpeta `public/mockups/` es referencia visual antigua. La app usa `resources/views/` (Blade).
