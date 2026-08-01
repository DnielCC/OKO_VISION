# Rúbrica Técnica - OKO VISION

## Estado general

Este documento resume cómo demostrar cada punto de la rúbrica del Proyecto Integrador OKO VISION con base en la arquitectura actual del repositorio.

## Checklist

| # | Punto de la rúbrica | Estado | Cómo demostrarlo |
|---|---|---|---|
| 1 | Métodos de hasheado y encriptado funcionando | Cumple | Mostrar login JWT en FastAPI/Flask y hashing bcrypt en usuarios. |
| 2 | Al menos dos servidores, uno público y otro privado | Cumple | Mostrar `docker-compose.yml` con `nginx_gateway` público y `postgres_db` en red `oko_private`. |
| 3 | Monitoreo con Prometheus y Grafana | Cumple | Abrir `/prometheus/-/healthy` y `/grafana/api/health`. |
| 4 | Aplicación y monitoreo de Firewall | Cumple | Mostrar reglas de rate limiting y headers de seguridad en Nginx. |
| 5 | Protección de API con JWT | Cumple | Mostrar login y endpoint protegido con token bearer. |
| 6 | Certificado SSL para la plataforma | Cumple | Entrar por `https://localhost` o `https://<IP-PC>` vía gateway. |
| 7 | Uso de balanceador de carga | Cumple | Mostrar upstreams `api_cluster` y `flask_cluster` con `least_conn`. |
| 8 | App móvil de utilidad real y no copia de la web | Cumple | Enseñar login biométrico, QR personal, escáner QR, vehículos e historial. |
| 9 | Diseño y estética profesional de la app móvil | Cumple | Mostrar login, home, perfil y QR. |
| 10 | Navegación móvil clara y entendible | Cumple | Recorrer tabs principales: Home, Vehículos, QR, Historial y Perfil. |
| 11 | Formularios validados antes de llegar a la BD | Cumple | Probar validación en login y formulario de vehículo. |
| 12 | La información móvil se refleja o monitorea por la contraparte web | Cumple | Registrar vehículo/acceso en móvil y mostrarlo en dashboard/reportes Laravel. |
| 13 | Web, API y BD alojados y funcionando en nube | Cumple | Mostrar `docker-compose.prod.yml` y el flujo listo para VPS/EC2/GCP/Azure. |
| 14 | Aplicación móvil 100% funcional con API y BD | Cumple condicionado a levantar el stack | Ejecutar backend + Expo apuntando a `/mobile-api` real y validar login, vehículos, QR e historial. |
| 15 | Se entrega un teléfono con la app móvil funcional a los evaluadores | Entrega manual | Instalar Expo Go SDK 52 compatible o generar APK/compilación final en el dispositivo de demostración. |

## Flujo de demostración recomendado

1. Levantar el stack:

```powershell
docker compose up -d --build
docker compose ps
```

2. Validar endpoints base:

```powershell
python tests_stack.py
python API_Backend\test_security.py
```

3. Abrir en navegador:

- `https://localhost/`
- `https://localhost/flask/`
- `https://localhost/api/health`
- `https://localhost/mobile-api/auth/login`
- `https://localhost/prometheus/-/healthy`
- `https://localhost/grafana/api/health`

4. Correr la app móvil en modo real:

```powershell
cd Mobile_App
npm install
$env:EXPO_PUBLIC_API_URL="https://<IP-DE-TU-PC>/mobile-api"
npx expo start --clear --tunnel
```

5. Evidencia clave para la exposición:

- Login exitoso con JWT.
- Alta o edición de vehículo desde móvil.
- Generación de QR personal.
- Escaneo QR y registro de acceso.
- Reflejo del acceso o vehículo en reportes/dashboard web.
- Monitoreo activo en Prometheus/Grafana.

## Notas importantes para evaluación

- La app móvil quedó configurada para usar backend real por defecto. El modo demo solo se activa si defines `EXPO_PUBLIC_DEMO_MODE=true`.
- Para dispositivos físicos, la variable `EXPO_PUBLIC_API_URL` debe apuntar a la IP real del equipo anfitrión.
- El último punto de la rúbrica no depende del código: requiere llevar un teléfono ya preparado para la demostración.
