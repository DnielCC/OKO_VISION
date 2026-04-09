# Plan de Integración: Visión por Computadora (YOLO, OCR y QR)
**Proyecto**: OKO VISION
**Entorno Principal**: FastAPI (Python), PostgreSQL, OpenCV.

Este documento detalla la arquitectura, tecnologías necesarias y pasos de implementación para integrar un motor de de detección basado en inteligencia artificial capaz de leer placas vehiculares y códigos QR desde cámaras.

---

## 1. Librerías y Dependencias Requeridas

Para implementar este módulo, el backend de Python (`API_Backend`) requerirá instalar las siguientes dependencias. Deben agregarse al archivo `requirements.txt`:

*   **Detección de Video / Cámaras (RTSP o Webcams):**
    *   `opencv-python`: Estándar para conectarse a las cámaras (`cv2.VideoCapture`), procesar los frames de video en tiempo real, dibujar sobre ellos o realizar recortes rápidos.
*   **Modelo de Detección de Vehículos y Placas (YOLO):**
    *   `ultralytics`: Librería oficial para cargar y ejecutar modelos modernos como YOLOv8 o YOLOv11. Se usará para ubicar espacialmente los vehículos y las placas.
*   **Reconocimiento Óptico de Caracteres (OCR):**
    *   `easyocr` o `pytesseract`: Encargados de convertir el recorte de la imagen de la placa en una cadena de texto (ej. "ABC-1234").
*   **Lectura de Códigos QR:**
    *   `pyzbar`: Herramienta óptima para procesar la imagen y extraer rápidamente la carga útil (payload) de los códigos QR.

---

## 2. Flujo de Trabajo (Pipeline) por Fotograma

El proceso completo para analizar en tiempo real el paso de un vehículo consiste en:

1.  **Captura de Fotograma**: Se extrae un frame de la transmisión de la cámara usando OpenCV.
2.  **Detección de QR**: La imagen pasa por la librería `pyzbar`. Si encuentra un código QR, procede a desencriptar o validarlo en base de datos.
3.  **Detección de Interés (YOLO)**: La imagen es procesada por YOLO, el cual devuelve las coordenadas `(x, y, ancho, alto)` que enmarcan una placa.
4.  **Extracción y Lectura de Placa**:
    *   Se extrae el fragmento exacto de la imagen que contiene la placa usando las coordenadas proporcionadas por YOLO.
    *   Se aplican filtros a la imagen para tener un buen contraste.
    *   El motor **OCR** analiza esta porción pequeña y devuelve el texto estructurado de la patente.
5.  **Registro en Base de Datos**: A través de las sesiones de SQLAlchemy de FastAPI, se almacena el evento registrando fecha, texto de la detección (QR o Placa) y validando accesos temporalmente.

---

## 3. Consideraciones de Arquitectura

Debido al alto consumo de recursos computacionales de la IA y el procesamiento de video, este proceso **no debe bloquear las solicitudes web HTTP habituales** del sistema (REST API).

### La Arquitectura Sugerida
*   **Worker en Segundo Plano (`video_worker.py`)**: Un servicio / script de Python paralelo al servidor HTTP. Este "Worker" es un bucle infinito dedicado exclusivamente al stream de video y a utilizar la tarjeta gráfica/CPU para las detecciones. Interacciona con la BD de Postgres de forma independiente.
*   **FastAPI**: Servirá los datos estructurados provenientes de Postgres a un panel administrativo hecho en Laravel (para generar reportes PDF, ver registros, etc). Funciona de interfaz limpia sin saturarse.

---

## 4. Implementación Base Transversal de Muestra

A continuación se expone un pseudo-código demostrando cómo coexisten YOLO, PyZbar y EasyOCR usando un único frame de video.

```python
import cv2
from ultralytics import YOLO
import easyocr
import pyzbar.pyzbar as pyzbar

# Configuración Inicial
reader = easyocr.Reader(['en', 'es']) 
model = YOLO('yolov8n_license_plates.pt') # Modelo entrenado específicamente para placas

# Conectarse a cámara (ID 0 web o URL RTSP)
cap = cv2.VideoCapture(0)

while cap.isOpened():
    ret, frame = cap.read()
    if not ret: break

    # -- 1. Procesamiento de Código QR --
    qrcodes = pyzbar.decode(frame)
    for qr in qrcodes:
        qr_data = qr.data.decode('utf-8')
        print(f"✅ EVENTO QR REGISTRADO: {qr_data}")
        # Lógica secundaria: Permiso de entrada para este QR temporal

    # -- 2. Procesamiento de Placas (ALPR) --
    results = model(frame) # Inferencia con YOLO
    
    for box in results[0].boxes:
        x1, y1, x2, y2 = map(int, box.xyxy[0]) # Coordenadas
        
        # Aislar (recortar) la zona de la placa para ayudar al OCR
        placa_recortada = frame[y1:y2, x1:x2]
        
        # Leer el contenido numérico / alfabeto
        ocr_result = reader.readtext(placa_recortada)
        if ocr_result:
            texto_placa = ocr_result[0][1]
            print(f"✅ EVENTO PLACA REGISTRADA: {texto_placa}")
            
            # (Opcional) Dibujar overlay en la fuente de video 
            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
            cv2.putText(frame, texto_placa, (x1, y1 - 10), 
                        cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 255, 0), 2)

    # Ventana de depuración (quitar en ambiente producción sin salida gráfica)
    cv2.imshow("Monitor OKO_VISION", frame)
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break # Detener si se presiona "q"

cap.release()
cv2.destroyAllWindows()
```

---

## 5. Próximos Pasos Proyectados
1.  **Adquisición o Entrenamiento del Modelo**: Ubicar un archivo de pesos `*.pt` pre-entrenado adaptado a las proporciones de las placas locales.
2.  **Módulo Worker de Prueba**: Crear el script en el entorno de desarrollo con conectividad a la cámara de la red.
3.  **Módulo de Persistencia**: Implementar la capa que interconecte el Worker de video con los Modelos Base de SQLAlchemy que ya conforman el backend.
