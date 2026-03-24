# OKO VISION - Estructura Docker

Este directorio contiene la configuración para desplegar OKO VISION usando Docker y Docker Compose.

## 🐳 Servicios Configurados

### 1. **Base de Datos MySQL**
- **Contenedor**: `oko_vision_db`
- **Puerto**: `3307`
- **Base de datos**: `oko_vision`
- **Usuario**: `oko_user`
- **Password**: `oko123`

### 2. **Aplicación Flask (Usuario)**
- **Contenedor**: `oko_vision_flask`
- **Puerto**: `5000`
- **Framework**: Flask 2.3.3
- **Python**: 3.11

### 3. **Aplicación Laravel (Administrador)**
- **Contenedor**: `oko_vision_laravel`
- **PHP**: 8.2-FPM
- **Framework**: Laravel 10

### 4. **Servidor Nginx**
- **Contenedor**: `oko_vision_nginx`
- **Puerto**: `8080`
- **Función**: Proxy inverso para Laravel

### 5. **API Gateway (Futuro)**
- **Contenedor**: `oko_vision_api`
- **Puerto**: `3000`
- **Framework**: Node.js
- **Estado**: Preparado para desarrollo

## 🚀 Comandos de Uso

### Iniciar todos los servicios
```bash
docker-compose up -d
```

### Incluir API Gateway (cuando esté disponible)
```bash
docker-compose --profile api up -d
```

### Ver logs
```bash
docker-compose logs -f flask_user
docker-compose logs -f laravel_admin
docker-compose logs -f db
```

### Detener servicios
```bash
docker-compose down
```

### Reconstruir imágenes
```bash
docker-compose build --no-cache
```

## 📁 Estructura de Directorios

```
OKO_VISION/
├── docker-compose.yml          # Orquestación de servicios
├── docker/
│   └── nginx/
│       └── default.conf        # Configuración Nginx
├── Flask_User/
│   ├── Dockerfile              # Configuración Docker Flask
│   ├── .dockerignore           # Archivos a ignorar
│   └── requirements.txt        # Dependencias Python
├── Laravel_Admin/
│   ├── Dockerfile              # Configuración Docker Laravel
│   └── .dockerignore           # Archivos a ignorar
└── database/
    └── init/                   # Scripts inicialización BD
```

## 🔗 URLs de Acceso

Una vez iniciados los servicios:

- **Flask (Usuario)**: http://localhost:5000
- **Laravel (Admin)**: http://localhost:8080
- **MySQL**: localhost:3307
- **API Gateway**: http://localhost:3000 (cuando esté disponible)

## 🔧 Variables de Entorno

Las variables de entorno están configuradas en `docker-compose.yml`:

### Base de Datos
- `MYSQL_DATABASE`: oko_vision
- `MYSQL_USER`: oko_user
- `MYSQL_PASSWORD`: oko123
- `MYSQL_ROOT_PASSWORD`: root123

### Flask
- `FLASK_ENV`: production
- `DATABASE_URL`: mysql+pymysql://oko_user:oko123@db:3306/oko_vision

### Laravel
- `DB_CONNECTION`: mysql
- `DB_HOST`: db
- `DB_DATABASE`: oko_vision
- `DB_USERNAME`: oko_user
- `DB_PASSWORD`: oko123

## 📝 Notas Importantes

1. **Base de Datos**: Los datos persisten en el volumen `mysql_data`
2. **Volúmenes**: Los directorios de código se montan para desarrollo en caliente
3. **Red**: Todos los servicios están en la red `oko_network`
4. **API Gateway**: Solo se inicia con el perfil `api`

## 🐛 Troubleshooting

### Si Laravel no funciona:
```bash
docker-compose exec laravel_admin php artisan cache:clear
docker-compose exec laravel_admin php artisan config:clear
docker-compose exec laravel_admin php artisan storage:link
```

### Si Flask no encuentra la BD:
```bash
docker-compose restart flask_user
```

### Permisos en Laravel:
```bash
docker-compose exec laravel_admin chown -R www-data:www-data storage bootstrap/cache
docker-compose exec laravel_admin chmod -R 775 storage bootstrap/cache
```
