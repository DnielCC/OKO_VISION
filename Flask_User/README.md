# OKO VISION - Sistema Usuario (Flask)

Sistema de gestión de accesos para usuarios desarrollado en Flask con interfaz moderna y responsiva.

## 🚀 Características

### 🔐 Autenticación
- Sistema de login para usuarios/estudiantes
- Sin formulario de registro (solo acceso)
- Sesiones seguras con Flask

### 👤 Perfil de Usuario
- Visualización de información personal
- Datos de matrícula y estatus
- Diseño centrado y minimalista

### 📱 QR de Acceso
- Generación de códigos QR personalizados
- Descarga e impresión del QR
- Información de vigencia y uso

### 🚗 Gestión de Vehículos
- Registro hasta 2 vehículos por usuario
- Información completa (marca, modelo, color, placa)
- Edición y eliminación de vehículos

### 📊 Historial de Accesos
- Registro completo de entradas y salidas
- Estadísticas y filtros
- Visualización detallada con timestamps

## 🛠️ Tecnologías Utilizadas

- **Backend**: Flask 2.3.3
- **Frontend**: HTML5, CSS3, JavaScript
- **Estilos**: CSS Grid, Flexbox, Animaciones
- **Iconos**: Font Awesome 6.4.0
- **Base de datos**: Simulada (listas Python)

## 📁 Estructura del Proyecto

```
Flask_User/
├── main.py              # Aplicación principal Flask
├── routes.py            # Rutas y lógica del sistema
├── server.py            # Servidor de demostración (sin Flask)
├── requirements.txt     # Dependencias Python
├── templates/           # Plantillas HTML
│   ├── login.html      # Página de login
│   ├── dashboard.html  # Panel principal (todo en uno)
│   ├── perfil.html     # Vista de perfil
│   ├── qr.html         # Generación de QR
│   ├── vehiculos.html  # Gestión de vehículos
│   └── historial.html  # Historial de accesos
└── static/             # Archivos estáticos (CSS, JS, imágenes)
```

## 🚀 Ejecución del Sistema

### Opción 1: Servidor de Demostración (Recomendado)
```bash
cd /home/dnts/Desktop/OKO_VISION/Flask_User
python3 server.py
```

### Opción 2: Servidor Flask (requiere instalación)
```bash
# Instalar dependencias
pip install -r requirements.txt

# Iniciar aplicación
python3 main.py
```

## 🔑 Usuarios de Prueba

| Usuario | Contraseña | Nombre |
|---------|------------|--------|
| alumno1 | 123456 | Juan Pérez |
| alumno2 | 123456 | María García |
| alumno3 | 123456 | Carlos López |

## 🌐 Rutas Disponibles

- `/` o `/login` - Login de usuario
- `/dashboard` - Panel principal con toda la información
- `/perfil` - Vista detallada del perfil
- `/qr` - Generación y descarga de QR
- `/vehiculos` - Gestión de vehículos (máx. 2)
- `/historial` - Historial completo de accesos
- `/logout` - Cerrar sesión

## 🎨 Diseño y UX

- **Diseño centrado**: Información concentrada en espacios optimizados
- **Dashboard unificado**: Todo el contenido principal en una sola vista
- **Interfaz moderna**: Gradientes, glassmorphism, animaciones suaves
- **Colores corporativos**: Azul cyan (#00F2FF), oscuro (#050A18)
- **Responsivo**: Adaptado para móviles y tablets
- **Accesibilidad**: Navegación clara y contrastes adecuados

## 📋 Funcionalidades por Vista

### Dashboard (Vista Principal)
- ✅ Perfil del usuario (nombre, matrícula, estatus)
- ✅ QR de acceso rápido
- ✅ Lista de vehículos registrados
- ✅ Historial reciente de accesos
- ✅ Navegación a vistas detalladas

### Perfil
- ✅ Información personal completa
- ✅ Avatar y datos de contacto
- ✅ Eatus de cuenta y verificación

### QR de Acceso
- ✅ Código QR personalizado
- ✅ Información de vigencia
- ✅ Opciones de descarga e impresión
- ✅ Instrucciones de uso

### Vehículos
- ✅ Lista de vehículos registrados
- ✅ Límite de 2 vehículos por usuario
- ✅ Información detallada por vehículo
- ✅ Opciones de edición y eliminación

### Historial
- ✅ Registro completo de accesos
- ✅ Estadísticas generales
- ✅ Filtros por tipo y fecha
- ✅ Visualización temporal detallada

## 🔧 Configuración

### Variables de Entorno
```python
# En main.py
app.config['SECRET_KEY'] = 'tu_clave_secreta_aqui'
```

### Base de Datos
Actualmente usando datos simulados en listas Python. Para producción:
- Configurar SQLAlchemy
- Crear modelos de base de datos
- Migrar datos existentes

## 🚀 Despliegue

### Desarrollo
```bash
python3 server.py
# Acceder a http://localhost:8080
```

### Producción
- Configurar servidor WSGI (Gunicorn, uWSGI)
- Configurar dominio y SSL
- Conectar base de datos real
- Optimizar assets y caché

## 🔄 Integración Futura

- **Base de Datos MySQL**: Conexión con sistema principal
- **API REST**: Endpoints para comunicación con Laravel
- **Sistema de IA**: Integración con reconocimiento de placas
- **Notificaciones**: Email/SMS para accesos
- **Reportes PDF**: Exportación de historial

## 📞 Soporte

Para soporte técnico o preguntas sobre el sistema:
- Revisar la documentación
- Verificar logs del servidor
- Contactar al equipo de desarrollo

---

**OKO VISION** - Sistema Inteligente de Control de Acceso
© 2025 - Todos los derechos reservados
