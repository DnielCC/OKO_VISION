# Reporte de Seguridad de la API - OKO VISION

## Fecha: 2026-07-10

---

## 1. Resumen Ejecutivo

Este reporte documenta el análisis de seguridad y las implementaciones realizadas en la API de OKO VISION para mejorar su seguridad.

---

## 2. Análisis de Vulnerabilidades Iniciales

Antes de las modificaciones, se identificaron las siguientes vulnerabilidades:

### 2.1 Falta de Autenticación en Endpoints
- **Descripción:** Todos los endpoints principales (usuarios, vehículos, accesos, personas) estaban accesibles públicamente sin ningún tipo de autenticación.
- **Impacto:** Cualquier persona podría acceder, modificar o eliminar datos sensibles del sistema.
- **Riesgo:** Alto.

> **📸 Captura 1: Endpoint público sin seguridad**  
> Toma una captura de pantalla de Postman/Insomnia/FastAPI docs accediendo a `/usuarios/` sin token y mostrando los datos (esto es la vulnerabilidad inicial).

### 2.2 Autenticación Básica Hardcodeada (Inutilizada)
- **Descripción:** Existía una implementación de autenticación HTTP Basic con credenciales hardcodeadas ("admin"/"1234") que no se utilizaba en ningún endpoint.
- **Impacto:** Si se hubiera utilizado, las credenciales estarían expuestas en el código.
- **Riesgo:** Medio.

### 2.3 Login Sin Token de Sesión
- **Descripción:** El endpoint de login devolvía información del usuario pero no generaba ningún token de sesión para autenticar solicitudes posteriores.
- **Impacto:** No había forma de mantener la sesión del usuario entre solicitudes.
- **Riesgo:** Alto.

> **📸 Captura 2: Login sin token (antes de la modificación)**  
> Toma una captura de la respuesta del endpoint `/auth/login` antes de los cambios, mostrando que no devuelve `access_token`.

---

## 3. Implementaciones Realizadas

### 3.1 Autenticación con JWT (JSON Web Token)

**Archivo modificado:** [API_Backend/app/security/auth.py](file:///c:/Users/Victus/OKO_VISION/API_Backend/app/security/auth.py)

**Cambios realizados:**
1. Eliminada la autenticación HTTP Basic hardcodeada.
2. Implementada la generación de tokens JWT con expiración (30 minutos).
3. Implementada la validación de tokens JWT en cada solicitud protegida.
4. Función `get_current_user` que valida el token y devuelve el usuario autenticado.

**Fragmento clave de código:**
```python
# Configuración JWT
SECRET_KEY = "your-secret-key-change-in-production-this-is-for-demo-only"
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30

def create_access_token(data: dict):
    to_encode = data.copy()
    expire = datetime.utcnow() + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    return encoded_jwt

def get_current_user(credentials: HTTPAuthorizationCredentials = Depends(security), db: Session = Depends(get_db)):
    # Valida el token y obtiene el usuario
    ...
```

> **📸 Captura 3: Código de seguridad JWT**  
> Toma una captura del archivo `security/auth.py` mostrando la implementación de JWT.

### 3.2 Mejora del Endpoint de Login

**Archivo modificado:** [API_Backend/app/router/auth.py](file:///c:/Users/Victus/OKO_VISION/API_Backend/app/router/auth.py)

**Cambios realizados:**
1. El endpoint `/auth/login` ahora genera y devuelve un token JWT.
2. El token se devuelve junto con la información del usuario.

**Respuesta del login:**
```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "bearer",
  "id": 1,
  "username": "admin",
  "email": "admin@example.com",
  ...
}
```

> **📸 Captura 4: Login exitoso con token JWT**  
> Toma una captura de la respuesta de `/auth/login` mostrando el `access_token`.

### 3.3 Protección de Endpoints

Se añadió la dependencia `get_current_user` a todos los endpoints importantes para requerir autenticación:

- **Usuarios:** `/usuarios/*`
- **Vehículos:** `/vehiculos/*`
- **Accesos:** `/accesos/*`
- **Personas:** `/personas/*`

> **📸 Captura 5: Intento de acceso sin token → Error 401**  
> Toma una captura de una solicitud a `/usuarios/` sin token, mostrando el error `401 Unauthorized`.

> **📸 Captura 6: Acceso exitoso con token válido → 200 OK**  
> Toma una captura de la misma solicitud, pero incluyendo el token en los headers (Authorization: Bearer ...), mostrando los datos correctos.

---

## 4. Pruebas Realizadas

### 4.1 Script de Pruebas
Se creó el archivo [API_Backend/test_security.py](file:///c:/Users/Victus/OKO_VISION/API_Backend/test_security.py) que realiza las siguientes pruebas:
1. Verifica que el endpoint raíz (`/`) sea público.
2. Verifica que los endpoints protegidos devuelvan 401 sin token.
3. Prueba el flujo completo de login y acceso con token válido.

> **📸 Captura 7: Ejecución del script de pruebas**  
> Toma una captura de la terminal ejecutando `python test_security.py` y mostrando los resultados exitosos.

### 4.2 Resultados Esperados
- Sin token: Todos los endpoints protegidos devuelven `401 Unauthorized`.
- Con token válido: Los endpoints responden correctamente (`200 OK`).

---

## 5. Recomendaciones para Producción

1. **Cambiar el SECRET_KEY:** Utilizar una variable de entorno para la clave secreta y no hardcodearla.
2. **HTTPS:** Implementar HTTPS en producción para cifrar las solicitudes y tokens.
3. **Autorización por Roles:** Implementar lógica de autorización para restringir acciones según el rol del usuario.
4. **Rate Limiting:** Añadir límite de solicitudes para prevenir ataques de fuerza bruta.
5. **Validación de Entradas:** Mejorar la validación de entradas de usuario para prevenir inyecciones SQL.

---

## 6. Conclusión General

Las implementaciones realizadas mejoran significativamente la seguridad de la API al requerir autenticación mediante tokens JWT para acceder a datos sensibles.

### 6.1 Conclusiones Individuales

- **Horta Martínez Axel Santiago**: Las implementaciones realizadas mejoran significativamente la seguridad de la API al requerir autenticación mediante tokens JWT para acceder a datos sensibles.
- **Cano Cabrera Eros Daniel**: La integración de JWT y la protección de endpoints refuerza la confidencialidad y el control de acceso a la información del sistema.
- **Atilano García María Carmen**: El uso de tokens con expiración y la validación en cada solicitud mitiga riesgos de acceso no autorizado y mejora la seguridad general.

---

## 7. Archivos Modificados y Creados

- Modificados:
  - `API_Backend/app/security/auth.py`
  - `API_Backend/app/router/auth.py`
  - `API_Backend/app/router/users.py`
  - `API_Backend/app/router/auto.py`
  - `API_Backend/app/router/access.py`
  - `API_Backend/app/router/personas.py`

- Creados:
  - `API_Backend/test_security.py`
  - `REPORTE_SEGURIDAD.md` (este archivo)
