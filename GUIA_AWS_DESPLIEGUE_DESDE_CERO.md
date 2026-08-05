# Guía AWS Paso A Paso Desde Cero para OKO VISION

Esta guía está pensada para que montes **OKO VISION** en un host de AWS **desde 0**, usando la opción más práctica para tu proyecto:

- **AWS EC2**
- **Ubuntu Server**
- **Docker Compose**
- **Elastic IP**

Tu proyecto no es una sola app simple. Incluye:

- `Laravel_Admin`
- `API_Backend` con FastAPI
- `Flask_User`
- `PostgreSQL`
- `Nginx`
- `Prometheus`
- `Grafana`

Por eso, la forma más sencilla y realista de subirlo a un host es con una **instancia EC2** que ejecute todo con Docker.

## 1. Qué necesitas antes de empezar

Antes de hacer nada, asegúrate de tener:

1. una cuenta de AWS
2. una tarjeta registrada en AWS
3. tu proyecto listo en tu PC
4. acceso a la carpeta:

```text
C:\Users\Victus\OKO_VISION
```

## 2. Qué vas a crear en AWS

Vas a crear esto:

1. una **instancia EC2**
2. una **Elastic IP** para que la IP no cambie
3. un **Security Group** con puertos abiertos
4. luego vas a conectarte por **SSH**
5. después vas a instalar Docker y subir tu proyecto

## 3. Crear la instancia EC2

### Paso 1: entrar a EC2

1. entra a `https://console.aws.amazon.com`
2. en el buscador escribe `EC2`
3. entra al servicio **EC2**
4. en el menú izquierdo entra a **Instances**
5. da clic en **Launch Instance**

### Paso 2: configurar la instancia

Llénalo así:

- **Name**: `oko-vision-prod`
- **AMI**: `Ubuntu Server 22.04 LTS` o `Ubuntu Server 24.04 LTS`
- **Instance type**:
  - mínimo: `t3.medium`
  - recomendado: `t3.large`

Recomendación real para tu proyecto:

- `t3.medium`: si solo quieres demo o exposición
- `t3.large`: si quieres más estabilidad

### Paso 3: crear la llave `.pem`

En **Key pair**:

1. clic en **Create new key pair**
2. nombre: `oko-vision-key`
3. tipo: `RSA`
4. formato: `.pem`
5. descárgala

Guárdala bien, por ejemplo:

```text
C:\Users\Victus\Downloads\oko-vision-key.pem
```

## 4. Configurar red y puertos

En **Network settings** selecciona o crea un Security Group nuevo.

Abre estos puertos:

1. `22` para SSH
2. `80` para HTTP
3. `443` para HTTPS

Déjalo así:

- `SSH` -> `22` -> **My IP**
- `HTTP` -> `80` -> `0.0.0.0/0`
- `HTTPS` -> `443` -> `0.0.0.0/0`

Luego en almacenamiento deja:

- **30 GB a 50 GB**

Después da clic en **Launch Instance**.

## 5. Asignar una Elastic IP

Esto es muy importante, porque la IP pública normal puede cambiar.

### Paso a paso

1. en EC2, menú izquierdo -> **Elastic IPs**
2. clic en **Allocate Elastic IP address**
3. clic en **Allocate**
4. selecciona esa nueva IP
5. clic en **Actions** -> **Associate Elastic IP**
6. elige tu instancia `oko-vision-prod`

Guarda esa IP. Esa será la que usarás.

## 6. Conectarte por SSH desde Windows

Abre PowerShell y ejecuta:

```powershell
ssh -i "C:\Users\Victus\Downloads\oko-vision-key.pem" ubuntu@TU_ELASTIC_IP
```

Ejemplo:

```powershell
ssh -i "C:\Users\Victus\Downloads\oko-vision-key.pem" ubuntu@3.145.100.20
```

Cuando pregunte si confías en el host, escribe:

```text
yes
```

## 7. Actualizar el servidor

Ya dentro de la EC2, ejecuta:

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y ca-certificates curl git unzip ufw
```

## 8. Instalar Docker

Ejecuta exactamente esto:

```bash
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo systemctl enable docker
sudo systemctl start docker
sudo usermod -aG docker $USER
```

Ahora sal del SSH:

```bash
exit
```

Y vuelve a entrar:

```powershell
ssh -i "C:\Users\Victus\Downloads\oko-vision-key.pem" ubuntu@TU_ELASTIC_IP
```

Verifica:

```bash
docker --version
docker compose version
```

## 9. Subir tu proyecto al servidor

Tienes dos formas.

### Opción A: si tu proyecto está en GitHub

```bash
git clone TU_REPOSITORIO
cd OKO_VISION
```

### Opción B: si lo vas a subir desde tu PC

Desde PowerShell en tu PC:

```powershell
scp -i "C:\Users\Victus\Downloads\oko-vision-key.pem" -r "C:\Users\Victus\OKO_VISION" ubuntu@TU_ELASTIC_IP:/home/ubuntu/
```

Luego entra por SSH:

```powershell
ssh -i "C:\Users\Victus\Downloads\oko-vision-key.pem" ubuntu@TU_ELASTIC_IP
```

Y entra a la carpeta:

```bash
cd /home/ubuntu/OKO_VISION
```

## 10. Preparar el entorno de producción

Dentro del servidor, ya en la carpeta del proyecto:

```bash
cp .env.prod.example .env
nano .env
```

Configura al menos estas variables:

```env
APP_URL=https://TU_ELASTIC_IP
SSL_CN=TU_ELASTIC_IP
SSL_SANS=IP:TU_ELASTIC_IP

DB_USER=oko_admin
DB_PASS=CAMBIA_ESTA_PASSWORD
DB_NAME=oko_vision

JWT_SECRET=CAMBIA_ESTE_SECRET_JWT
FLASK_SECRET=CAMBIA_ESTE_SECRET_FLASK
GRAFANA_PASS=CAMBIA_ESTA_PASSWORD_GRAFANA

UVICORN_WORKERS=2
GUNICORN_WORKERS=2
GUNICORN_THREADS=4
```

Sugerencia:
- usa passwords largas
- no dejes los valores por defecto

Para guardar en `nano`:

1. `Ctrl + O`
2. Enter
3. `Ctrl + X`

## 11. Desplegar el proyecto

Si existe el script `deploy_prod.sh`, úsalo así:

```bash
chmod +x deploy_prod.sh
./deploy_prod.sh
```

Si prefieres levantarlo manualmente:

```bash
docker compose -f docker-compose.prod.yml --env-file .env up -d --build
```

## 12. Verificar que todos los contenedores estén arriba

Ejecuta:

```bash
docker compose -f docker-compose.prod.yml --env-file .env ps
```

Y para ver logs:

```bash
docker compose -f docker-compose.prod.yml --env-file .env logs --tail=100
```

Si quieres revisar solo Nginx:

```bash
docker compose -f docker-compose.prod.yml --env-file .env logs --tail=100 nginx_gateway
```

## 13. Probar el proyecto en navegador

Desde tu PC o tu celular, prueba:

```text
https://TU_ELASTIC_IP
https://TU_ELASTIC_IP/login
https://TU_ELASTIC_IP/flask/login
https://TU_ELASTIC_IP/api/health
```

También puedes probar:

```text
https://TU_ELASTIC_IP/grafana/
```

Si abre con advertencia de seguridad, es normal si estás usando certificado autofirmado.

## 14. Configurar la app móvil para AWS

En tu proyecto local, cambia la URL de la app móvil para que ya no apunte a tu red local ni a `localhost`.

En:

```text
Mobile_App/.env
```

deja algo así:

```env
EXPO_PUBLIC_API_URL=https://TU_ELASTIC_IP/mobile-api
```

Después corre otra vez Expo:

```powershell
cd "C:\Users\Victus\OKO_VISION\Mobile_App"
npx expo start --clear
```

## 15. Si quieres dominio después

Primero puedes trabajar solo con la IP.

Más adelante, si compras un dominio:

1. apuntas el dominio a la Elastic IP
2. cambias `.env`
3. reemplazas:

```env
APP_URL=https://tu-dominio.com
SSL_CN=tu-dominio.com
SSL_SANS=DNS:tu-dominio.com,DNS:www.tu-dominio.com,IP:TU_ELASTIC_IP
```

## 16. Problemas comunes

### No conecta por SSH

Revisa:

- que estés usando la IP correcta
- que el puerto `22` esté abierto
- que uses `ubuntu@IP`
- que la `.pem` sea la correcta

### No abre en navegador

Revisa:

- que la instancia esté encendida
- que tenga Elastic IP asociada
- que `80` y `443` estén abiertos
- que Docker Compose esté levantado

### La app móvil no conecta

Revisa:

- que `EXPO_PUBLIC_API_URL` apunte a la Elastic IP pública
- que el gateway esté arriba
- que `https://TU_ELASTIC_IP/mobile-api/auth/login` responda

## 17. Comandos útiles

Ver contenedores:

```bash
docker ps
```

Reiniciar stack:

```bash
docker compose -f docker-compose.prod.yml --env-file .env down
docker compose -f docker-compose.prod.yml --env-file .env up -d --build
```

Ver logs en vivo:

```bash
docker compose -f docker-compose.prod.yml --env-file .env logs -f
```

## 18. Resumen corto

La secuencia correcta es:

1. crear EC2
2. crear Elastic IP
3. abrir puertos `22`, `80`, `443`
4. conectarte por SSH
5. instalar Docker
6. subir tu proyecto
7. configurar `.env`
8. levantar `docker-compose.prod.yml`
9. probar en navegador
10. apuntar la app móvil al host público

## 19. Qué sigue después

Cuando ya tengas creada tu instancia EC2, lo siguiente que vas a necesitar compartir para seguir contigo paso por paso es:

1. la **Elastic IP**
2. la ruta de tu archivo `.pem`
3. si vas a usar solo IP o también dominio

Con eso te acompaño ya sobre tu caso real hasta dejar **OKO VISION** publicado.
