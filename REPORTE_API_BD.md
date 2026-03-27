# Reporte de Implementación: API y Base de Datos OKO VISION

## 1. Arquitectura de Contenedores (Docker)
El proyecto ha sido actualizado para incluir una arquitectura de microservicios utilizando Docker. Se han configurado los siguientes contenedores:

- **`postgres_db`**: Contenedor oficial de PostgreSQL 16-alpine.
  - **Puerto**: 5432
  - **Base de Datos**: `oko_vision`
  - **Persistencia**: Volumen `postgres_data` para evitar pérdida de datos.
- **`api_backend`**: Contenedor personalizado para la API construida con FastAPI.
  - **Puerto**: 8002 (interno 8000)
  - **Framework**: FastAPI (Python 3.11)
  - **ORM**: SQLAlchemy 2.0
- **`flask_user`**: Portal de usuario (Web1).
- **`laravel_admin`**: Portal administrativo (Web2).

## 2. Modelos de Datos (SQLAlchemy)
Se han implementado modelos relacionales utilizando SQLAlchemy para mapear la base de datos PostgreSQL:

- **User**: Gestión de credenciales, roles (admin/user) y metadatos.
- **Vehicle**: Registro de placas, marcas, modelos y relación con el propietario.
- **AccessLog**: Historial detallado de entradas y salidas, placas detectadas y estatus de autorización.
- **Alert**: Registro de eventos críticos del sistema (intrusiones, fallos de lectura).

## 3. Endpoints de la API (FastAPI)
La API expone los siguientes puntos de acceso principales para ser consumidos por los portales:

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/` | Estado de salud de la API |
| POST | `/users/` | Registro de nuevos usuarios |
| GET | `/users/` | Listado de usuarios registrados |
| GET | `/vehicles/` | Consulta de vehículos autorizados |
| GET | `/access-logs/` | Historial de accesos en tiempo real |
| GET | `/alerts/` | Listado de alertas de seguridad |

## 4. Integración Web1 y Web2
Ambos portales (Flask y Laravel) han sido integrados en la misma red de Docker (`oko_network`), permitiendo que se comuniquen con el `api_backend` de forma interna y segura para realizar operaciones de lectura y escritura en la base de datos centralizada de PostgreSQL.
