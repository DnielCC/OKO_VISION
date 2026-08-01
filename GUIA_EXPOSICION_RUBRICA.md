# Guía de Exposición - Rúbrica OKO VISION

## Preparación antes de exponer

1. Abre Docker Desktop y verifica que esté iniciado.
2. En la raíz del proyecto ejecuta:

```powershell
docker compose up -d --build
docker compose ps
python tests_stack.py
python API_Backend\test_security.py
```

3. Para la app móvil, en otra terminal:

```powershell
cd C:\Users\Victus\OKO_VISION\Mobile_App
npm install
$env:EXPO_PUBLIC_API_URL="https://<IP-DE-TU-PC>/mobile-api"
npx expo start --clear --tunnel
```

4. En el celular:

- abre Expo Go SDK 52,
- escanea el QR,
- inicia sesión con un usuario válido.

## Flujo de exposición por punto

### 1. Hasheado y encriptado funcionando

Demuestra:
- login con credenciales válidas,
- token JWT devuelto por backend,
- contraseñas almacenadas con bcrypt.

Qué enseñar:
- `API_Backend/app/security/auth.py`
- `Laravel_Admin/verify.php`

Frase sugerida:
> El sistema no guarda contraseñas en texto plano; se usa bcrypt para hash seguro y JWT para autenticar las APIs.

### 2. Dos servidores: público y privado

Demuestra:
- `nginx_gateway` expuesto en `80` y `443`,
- `postgres_db` en red `oko_private`.

Qué enseñar:
- `docker-compose.yml`
- `docker-compose.prod.yml`

Comando:

```powershell
docker compose ps
```

Frase sugerida:
> El único servicio públicamente expuesto es Nginx; la base de datos queda aislada en red privada.

### 3. Monitoreo con Prometheus y Grafana

Abre:
- `https://localhost/prometheus/-/healthy`
- `https://localhost/grafana/api/health`

Frase sugerida:
> El proyecto incluye monitoreo operativo para servicios, contenedores y métricas de aplicación.

### 4. Firewall y monitoreo de firewall

Demuestra:
- rate limiting en Nginx,
- cabeceras de hardening.

Qué enseñar:
- `docker/nginx/nginx.conf`

Frase sugerida:
> Implementamos un firewall de capa 7 con límites de conexión y peticiones, además de headers de seguridad.

### 5. Protección de API con JWT

Demuestra:
- login,
- consumo de un endpoint protegido con bearer token.

Abre:
- `https://localhost/api/health`
- flujo de login desde la app móvil o Postman.

### 6. Certificado SSL

Demuestra:
- acceso por `https://localhost`,
- gateway terminando TLS.

Qué enseñar:
- `docker/nginx/conf.d/default.conf`
- `docker/nginx/entrypoint.sh`

### 7. Balanceador de carga

Demuestra:
- upstream `api_cluster`,
- upstream `flask_cluster`,
- estrategia `least_conn`.

Qué enseñar:
- `docker/nginx/nginx.conf`

### 8. La app móvil es útil y no copia la web

Pantallas a abrir:
- Login con biometría
- Home
- Vehículos
- QR personal
- Escáner QR
- Historial

Frase sugerida:
> La app aporta funciones móviles reales: biometría, QR, escaneo y consulta operativa en campo.

### 9. Diseño y estética profesional

Muestra:
- Login
- Home
- Perfil
- QR

Habla de:
- modo oscuro,
- consistencia visual,
- componentes reutilizables,
- experiencia pensada para exposición.

### 10. Navegación móvil clara

Haz el recorrido:
1. Home
2. Vehículos
3. QR
4. Historial
5. Perfil

Frase sugerida:
> La navegación se organizó por tareas reales de uso y permite llegar a cada módulo con pocos toques.

### 11. Formularios validados antes de guardar en BD

Prueba:
- intentar registrar vehículo con datos inválidos,
- luego registrar uno válido.

Qué enseñar:
- validaciones en móvil,
- validaciones backend.

Archivos:
- `Mobile_App/src/utils/validators.ts`
- `API_Backend/app/models/cars.py`

### 12. La información de la app móvil se refleja en la web

Prueba recomendada:
1. Crear o editar un vehículo desde móvil.
2. Registrar un acceso desde QR o flujo de acceso.
3. Ir al dashboard/reportes en Laravel.
4. Mostrar que el vehículo o acceso aparece en la contraparte web.

Esto ya quedó respaldado con la sincronización:
- `API_Backend/app/router/auto.py`
- `API_Backend/app/router/access.py`

### 13. Web, API y BD listos para nube

Demuestra:
- archivo `docker-compose.prod.yml`,
- variables para producción,
- rutas públicas esperadas.

Frase sugerida:
> El sistema ya está preparado para desplegarse en una VPS o instancia cloud con HTTPS, monitoreo y segmentación de red.

### 14. App móvil 100% funcional con API y BD

Haz esta prueba en vivo:
1. login,
2. consultar home,
3. ver vehículos,
4. crear/editar vehículo,
5. generar QR,
6. abrir historial.

Si el backend está arriba y la variable `EXPO_PUBLIC_API_URL` apunta al gateway, este punto queda cubierto.

### 15. Teléfono entregado con la app funcional

Esto no se demuestra en código; se resuelve con preparación logística:
- llevar el celular listo,
- Expo Go compatible instalado o build final disponible,
- sesión iniciada o credenciales a la mano.

## Prueba rápida final antes de entrar

Haz esto 5 minutos antes:

```powershell
docker compose ps
python tests_stack.py
```

Y revisa manualmente:
- `https://localhost/`
- `https://localhost/api/health`
- `https://localhost/flask/`
- `https://localhost/prometheus/-/healthy`
- `https://localhost/grafana/api/health`

## Orden ideal de exposición

1. Arquitectura y redes
2. Seguridad: hash, JWT, SSL, firewall
3. Monitoreo y balanceo
4. Web administrativa
5. App móvil
6. Sincronización móvil -> web
7. Despliegue en nube

## Capturas o evidencia que más ayudan

- `docker compose ps`
- Login móvil
- Home móvil
- Vehículos móvil
- QR móvil
- Historial móvil
- Dashboard Laravel
- Reportes Laravel
- Prometheus healthy
- Grafana healthy
