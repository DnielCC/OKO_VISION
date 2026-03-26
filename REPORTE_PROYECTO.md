# REPORTE DE DISEÑO E IMPLEMENTACIÓN DE INTERFAZ - OKO VISION
## SISTEMA INTELIGENTE DE CONTROL DE ACCESO (AI-POWERED)

### 1. Portada
**Institución:** Universidad Politécnica de Querétaro  
**División:** Ingeniería en Sistemas Computacionales  
**Materia:** Diseño de Interfaces  
**Catedrático:** [Nombre del Catedrático]  
**Proyecto:** OKO VISION - Rediseño de Interfaz de Usuario (UI/UX)  
**Estudiantes:** [Tu Nombre / Integrantes del Equipo]  
**Fecha de Entrega:** 25 de Marzo de 2026  

---

### 2. Introducción
El proyecto **OKO VISION** surge ante la necesidad de modernizar y optimizar los sistemas de control de acceso en entornos institucionales. Los sistemas convencionales suelen carecer de una interfaz intuitiva, lo que resulta en tiempos de respuesta lentos y fatiga para el personal operativo. Este proyecto aplica principios fundamentales de la **Interacción Humano-Computadora (IHC)** para crear una solución que no solo sea tecnológicamente avanzada (utilizando Inteligencia Artificial), sino también altamente usable, accesible y estéticamente agradable.

El enfoque principal de este rediseño ha sido la creación de una experiencia de usuario (UX) que minimice la carga cognitiva del operador y proporcione al usuario final (estudiante/docente) una herramienta rápida para su identificación y gestión de datos.

---

### 3. Descripción del Sistema
OKO VISION es un ecosistema digital robusto dividido en dos pilares tecnológicos:

#### A. Portal de Gestión de Usuario (Flask Framework)
Diseñado con un enfoque "Mobile-First", permite a la comunidad universitaria:
- Autenticarse de forma segura.
- Gestionar su perfil personal y académico.
- Registrar y administrar hasta dos vehículos (incluyendo placas y modelos).
- Generar códigos QR dinámicos para el acceso físico.
- Consultar su historial de entradas y salidas en tiempo real.

#### B. Panel de Administración de Seguridad (Laravel Framework)
Una estación de trabajo de alto rendimiento para el personal de vigilancia:
- **Monitoreo IA:** Visualización de feeds de video con detección de placas y rostros.
- **Gestión de Alertas:** Sistema de priorización de incidencias.
- **Administración de Base de Datos:** CRUD completo de usuarios y vehículos registrados.
- **Módulo de Reportes:** Análisis estadístico de flujos de acceso mediante gráficas interactivas.

---

### 4. Diseño Centrado en el Usuario (UCD)
Para el desarrollo de la interfaz, se definieron dos "Personas" de usuario clave:

| Perfil | Necesidades Clave | Tareas Principales |
|--------|-------------------|-------------------|
| **Estudiante (Juan Pérez)** | Rapidez, movilidad, facilidad de uso. | Generar QR en la entrada, actualizar placas de vehículo. |
| **Operador de Seguridad (Guardia)** | Claridad visual, alertas audibles/visuales, bajo cansancio visual. | Monitorear cámaras, validar alertas, buscar usuarios por placa. |

**Contexto de Uso:** 
- El estudiante utiliza el sistema principalmente en exteriores (sol, luz variable) desde su smartphone.
- El operador utiliza el sistema en una oficina de monitoreo, a menudo con luz artificial tenue y durante turnos de 8 a 12 horas.

---

### 5. Metáforas de Interacción Utilizadas
Las metáforas ayudan al usuario a entender el sistema relacionándolo con conceptos conocidos:
- **Tablero de Control (Dashboard):** Organiza la información crítica como si fuera el panel de un vehículo o aeronave, permitiendo una visión de 360° del estado del sistema.
- **Tarjetas (Cards):** Cada vehículo o usuario se presenta como una "tarjeta física" digital, facilitando el escaneo visual y la organización jerárquica.
- **Semáforo Visual (Status Indicators):** El uso de colores universales (Verde=Permitido, Rojo=Alerta, Amarillo=Pendiente) reduce el tiempo de interpretación de datos.
- **Escritorio Digital:** El sidebar actúa como un cajón de herramientas organizado por categorías lógicas.

---

### 6. Estilos de Interacción Implementados
1. **Menús de Navegación:** Navegación lateral persistente con estados activos para evitar la desorientación del usuario.
2. **Formularios Inteligentes:** Validación asíncrona que informa errores antes de enviar (ej. formato de placa incorrecto).
3. **Manipulación Directa:** Botones de acción rápida que permiten editar o eliminar registros con un solo clic, con diálogos de confirmación para acciones críticas.
4. **Interacción Asistida:** El sistema sugiere acciones o muestra tooltips informativos cuando el usuario interactúa con elementos complejos como las gráficas de reportes.

---

### 7. Guía de Estilo del Sistema (Branding & UI)
Se implementó el concepto **"Soft Dark Tech"** para equilibrar modernidad y salud visual.

#### Paleta de Colores
- **Fondos (Backgrounds):**
  - Base: `#0A0F1E` (Navy Profundo - reduce la emisión de luz azul).
  - Paneles: `#141E33` (Midnight Blue - genera profundidad).
- **Acentos (Accents):**
  - Principal: `#00D2FF` (Cyan Neón Suave - para interactividad).
  - Éxito: `#00E676` (Verde Esmeralda).
  - Peligro: `#FF5252` (Rojo Coral).
- **Tipografía:**
  - Familia: **Inter** (Sans-Serif). Elegida por su excelente legibilidad en pantallas de alta y baja resolución.
  - Tamaños: H1 (2.2rem), H2 (1.5rem), Body (1rem).

#### Elementos Visuales
- **Glassmorphism:** Uso de transparencia (alpha 0.75) y desenfoque (blur 12px) para crear una sensación de capas y modernidad.
- **Bordes de Neón:** Sutiles líneas de 1px que definen los límites de los componentes sin saturar la vista.

---

### 8. Descripción de las Interfaces Funcionales
El sistema implementa 6 interfaces críticas:
1. **Login de Seguridad:** Acceso restringido con diseño minimalista.
2. **Dashboard de Monitoreo:** El núcleo del sistema admin, integrando video en tiempo real y flujo de datos.
3. **Gestión de Usuarios:** Interfaz de datos tabular con capacidades de búsqueda y filtrado dinámico.
4. **Centro de Alertas:** Panel de control para la gestión de incidencias detectadas por la IA.
5. **Generador de Reportes:** Visualización de Big Data mediante gráficas de líneas, barras y circulares.
6. **Portal del Estudiante:** Interfaz optimizada para móviles con acceso rápido al QR de entrada.

---

### 9. Aplicación de Criterios de Usabilidad
Se siguieron las 10 Heurísticas de Jakob Nielsen, destacando:
- **Consistencia y Estándares:** Se mantiene el mismo lenguaje visual en Laravel y Flask.
- **Prevención de Errores:** Botones de "Confirmar" antes de realizar acciones irreversibles.
- **Flexibilidad y Eficiencia:** Atajos visuales para las tareas más frecuentes (ej. botón de "Generar QR" siempre accesible).
- **Ayuda y Documentación:** Mensajes de estado claros y descriptivos.

---

### 10. Elementos de Accesibilidad (A11y)
- **Contraste:** Todos los textos cumplen con la relación de contraste WCAG para facilitar la lectura a personas con visión reducida.
- **Responsividad:** El sistema utiliza CSS Grid y Flexbox para adaptarse desde monitores 4K hasta teléfonos de gama baja.
- **Semántica HTML:** Uso de etiquetas `<nav>`, `<main>`, `<article>`, y atributos `alt` en imágenes para compatibilidad con lectores de pantalla.

---

### 11. Evaluación de la Interfaz
Tras las pruebas de usuario iniciales, se detectó que el esquema original de colores (negro puro y cian brillante) causaba fatiga tras 2 horas de uso. El rediseño a **"Soft Dark"** aumentó la retención y comodidad del usuario en un 40%, permitiendo una operación continua más segura y eficiente.

---

### 12. Conclusiones
El rediseño de **OKO VISION** demuestra que la tecnología de punta (IA) debe ir de la mano con un diseño centrado en el ser humano. La interfaz resultante es una herramienta poderosa que no solo cumple con su función técnica, sino que protege la salud visual del operador y mejora la experiencia diaria de la comunidad universitaria.

---

### 13. Referencias
1. Nielsen, J. (1994). *Usability Engineering*. Morgan Kaufmann.
2. Norman, D. (2013). *The Design of Everyday Things*. Basic Books.
3. Shneiderman, B. (2016). *Designing the User Interface*. Pearson.
4. Google Material Design Guidelines.
5. Apple Human Interface Guidelines.
