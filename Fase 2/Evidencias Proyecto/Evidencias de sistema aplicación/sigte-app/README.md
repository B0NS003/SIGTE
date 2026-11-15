# SIGTE

**Sistema Integral de Gestión y Trazabilidad de Esterilización**  
Hospital San José de Melipilla

Aplicación web para el seguimiento del ciclo de cajas e insumos de la Central de Esterilización: recepción, flujo por etapas, entrega a servicios clínicos, catálogo, inventario, turnos, producción y reportes.

---

## Stack

| Capa | Tecnología |
|------|------------|
| Backend | PHP 8.2+, Laravel 11 |
| Vista | Blade |
| Base de datos | PostgreSQL 16 |
| Entorno local | Docker (`sigte-app`, `sigte-db`) |
| Exportes | Excel (PhpSpreadsheet), PDF (DomPDF) |

---

## Requisitos

- Docker Desktop
- Archivo `.env` configurado (no versionar credenciales reales)

---

## Arranque local

```bash
docker compose up -d --build
```

| Servicio | URL / puerto |
|----------|----------------|
| Aplicación | http://localhost:8080 |
| Login | http://localhost:8080/login |
| PostgreSQL | `localhost:5432` (usuario/BD: `sigte`) |

Migraciones (con el contenedor en marcha):

```bash
docker exec sigte-app php artisan migrate
```

Semilla de datos demo (solo entornos de desarrollo):

```bash
docker exec sigte-app php artisan db:seed
```

---

## Roles

| Rol | Alcance principal |
|-----|-------------------|
| Administradora | Usuarios, turnos, catálogo, reportes, inventario, alertas |
| Enfermera de turno | Supervisión de turno, inventario/reposiciones, entregas, alertas |
| Operador / Operadora | Flujo de cajas, inventario operativo (con cuenta de puesto si aplica) |
| Secretaria | Producción, insumos y reportes estadísticos |

---

## Cuentas demo (desarrollo)

Contraseña de todas: `password`

| Correo | Rol |
|--------|-----|
| `admin@sigte.local` | Administradora |
| `enfermera@sigte.local` | Enfermera de turno |
| `operador@sigte.local` | Cuenta del puesto (operación compartida) |
| `secretaria@sigte.local` | Secretaria |

---

## Estructura relevante

```
app/                  Lógica de negocio y controladores
database/migrations/  Esquema PostgreSQL (no eliminar historial)
resources/views/      Vistas Blade por módulo
public/css/           Tokens, componentes y CSS por módulo
public/js/            JavaScript por módulo
routes/web.php        Rutas de la aplicación
```

---

## Producción

1. Respaldo de la base de datos antes de desplegar.
2. Subir código **sin** el `.env` local.
3. Ejecutar migraciones pendientes.
4. Limpiar cachés (`optimize:clear`) y reiniciar PHP/Apache del contenedor si hace falta (OPcache).

Compose de producción: `docker-compose.prod.yml`.

---

## Documentación de trabajo

Notas de auditoría BD, reorganización CSS/JS, `AGENTS.md` y guías tipográficas/visuales se conservan fuera de este repositorio:

`Documents/SIGTE/Fase 2/Documentacion-tecnica-sigte-app/`

Pueden reintegrarse al proyecto cuando se retomen correcciones o mejoras.
