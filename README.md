# OKO VISION - Sistema Inteligente de Control de Acceso

Un sistema moderno de control de acceso automatizado con inteligencia artificial, desarrollado con Laravel y Blade. Interfaz frontend con estética Deep Tech y modo oscuro.

## 🎨 Identidad Visual

### Paleta de Colores
- **Fondo Base**: `#050A18` (Deep Navy)
- **Acento Principal**: `#00F2FF` (Cyan Neón) 
- **Paneles/Tarjetas**: `#0D1B35` (Midnight Blue)
- **Texto**: `#E0E6ED` (Off-White)
- **Éxito**: `#00FF87` (Verde)
- **Alerta**: `#FF3131` (Rojo Neón)

### Características de Diseño
- Glassmorphism con transparencias sutiles
- Efectos de glow y sombras suaves en elementos cian
- Totalmente responsive (mobile-friendly)
- Tipografía Inter (Sans-Serif limpia)
- Animaciones y transiciones fluidas

## 📁 Estructura del Proyecto

```
resources/views/
├── layouts/
│   └── app.blade.php          # Layout principal con sidebar
├── auth/
│   └── login.blade.php        # Pantalla de login minimalista
├── dashboard.blade.php        # Dashboard con video feed y accesos
├── alertas.blade.php          # Gestión de incidencias y alertas
├── usuarios.blade.php         # CRUD de usuarios y vehículos
├── reportes.blade.php         # Estadísticas y reportes
└── welcome.blade.php          # Vista por defecto de Laravel
```

## 🚀 Funcionalidades Implementadas

### 1. Layout Principal (`layouts/app.blade.php`)
- Sidebar fijo con navegación glassmorphism
- Sistema de notificaciones
- Perfil de usuario
- Menú móvil responsive
- Indicadores de estado del sistema

### 2. Login (`auth/login.blade.php`)
- Diseño minimalista con fondo animado
- Bordes neón y efectos glow
- Validación de formularios
- Animaciones de carga
- Recordar contraseña

### 3. Dashboard (`dashboard.blade.php`)
- **Video Feed Principal**: Placeholder con simulación de detección IA
- **Detección en Tiempo Real**: Cuadros cian sobre el video
- **Sidebar de Accesos**: Lista de últimos accesos con badges
- **Estadísticas en Tiempo Real**: Accesos, alertas, tasa de detección
- **Actividad del Sistema**: Timeline de eventos recientes
- **Controles de Cámara**: Selección múltiple de cámaras

### 4. Alertas (`alertas.blade.php`)
- **Filtros Avanzados**: Por tipo, fecha, estado
- **Tabla de Incidencias**: Con badges de colores (Rojo/Amarillo/Verde)
- **Acciones Rápidas**: Validar y Reportar alertas
- **Estadísticas**: Totales y distribución por severidad
- **Búsqueda en Tiempo Real**: Filtrado dinámico
- **Paginación**: Navegación eficiente de registros

### 5. Usuarios (`usuarios.blade.php`)
- **CRUD Completo**: Crear, leer, actualizar, eliminar usuarios
- **Gestión de Vehículos**: Hasta 2 placas por usuario
- **Formulario Avanzado**: Datos personales, vehículos, permisos
- **Subida de Archivos**: Fotos de placas
- **Permisos de Acceso**: Días y horarios configurables
- **Búsqueda y Filtros**: Por nombre, email, placa, rol, estado

### 6. Reportes (`reportes.blade.php`)
- **Dashboard de Estadísticas**: 3 gráficas principales
- **Métricas Clave**: Accesos, usuarios, alertas, tasa de detección
- **Gráficas Simuladas**:
  - Líneas: Tendencia de accesos
  - Circular: Distribución por tipo
  - Barras: Actividad por hora
- **Tablas Detalladas**: Top usuarios, resumen de alertas
- **Exportación**: PDF y Excel con modal de configuración
- **Filtros de Fecha**: Por período o rango personalizado

## 🛠️ Tecnologías Utilizadas

### Frontend
- **HTML5** Semántico y accesible
- **Tailwind CSS** para estilos modernos
- **Font Awesome 6** para iconos
- **JavaScript Vanilla** para interactividad
- **CSS3 Animations** para efectos visuales

### Backend (Laravel)
- **Blade Templates** para renderizado
- **Sistema de Rutas** Laravel
- **Middleware de Autenticación**
- **CSRF Protection**

## 🎯 Características Técnicas

### Responsive Design
- Mobile-first approach
- Breakpoints: sm, md, lg, xl
- Menú hamburguesa para móviles
- Tables scrollables horizontalmente

### Interactividad
- Animaciones CSS3 suaves
- Transiciones hover states
- Modales para formularios
- Notificaciones toast
- Auto-refresh de datos

### Accesibilidad
- Estructura semántica HTML5
- ARIA labels donde es necesario
- Contraste de colores optimizado
- Navegación por teclado

## 📱 Vista Previa de Interfaces

### Login
- Fondo con animaciones flotantes
- Logo con efecto pulse
- Campos con bordes neón
- Botón con efecto shimmer

### Dashboard
- Video feed con overlay de detección IA
- Tarjetas de estadísticas con iconos
- Lista de accesos en tiempo real
- Timeline de actividad del sistema

### Alertas
- Tabla con filas coloreadas por severidad
- Badges de estado animados
- Filtros múltiples
- Búsqueda instantánea

### Usuarios
- Tabla CRUD con avatares
- Modal de formulario complejo
- Gestión de múltiples vehículos
- Configuración de permisos

### Reportes
- Dashboard con múltiples gráficas
- Métricas con indicadores de tendencia
- Tablas de datos detallados
- Modal de exportación

## 🚀 Instalación y Uso

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd OKO_VISION
```

2. **Instalar dependencias**
```bash
composer install
npm install
```

3. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Compilar assets**
```bash
npm run build
```

5. **Iniciar servidor**
```bash
php artisan serve
```

## 📝 Notas de Desarrollo

- Las interfaces son completamente funcionales como prototipos
- Los datos son simulados/dummy para demostración
- Las gráficas usan placeholders (listas para integración con Chart.js/D3.js)
- Los formularios tienen validación frontend
- El diseño está preparado para integración con backend real

## 🔮 Próximos Pasos

1. **Integración Backend**: Conectar con APIs reales
2. **Base de Datos**: Implementar modelos y migraciones
3. **Autenticación**: Completar sistema de login/registro
4. **Gráficas Reales**: Integrar Chart.js o similar
5. **Notificaciones Push**: Sistema de alertas en tiempo real
6. **Optimización**: Lazy loading y performance

---

**OKO VISION** - El futuro del control de acceso inteligente 🚪🤖✨
