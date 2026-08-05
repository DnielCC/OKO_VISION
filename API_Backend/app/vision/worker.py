"""
Módulo de Trabajador de IA de Visión para OKO Vision
Contiene funciones para procesamiento de imágenes con YOLOv8, detección de vehículos, autorización de accesos, etc.
"""
from typing import Dict, Any, List
import uuid
import base64
import io
import cv2
import numpy as np
from PIL import Image
from ultralytics import YOLO

modelo = None
modelo_carga_intentada = False


def _obtener_modelo():
    global modelo, modelo_carga_intentada

    if modelo is not None:
        return modelo

    if modelo_carga_intentada:
        return None

    modelo_carga_intentada = True
    try:
        # Cargar el modelo bajo demanda evita que el contenedor falle
        # durante el healthcheck inicial en instancias pequeñas.
        modelo = YOLO("yolov8n.pt")
    except Exception as e:
        print(f"Error al cargar el modelo YOLO: {e}")
        modelo = None

    return modelo

try:
    cascade_classifier = getattr(cv2, "CascadeClassifier", None)
    haar_path = getattr(getattr(cv2, "data", None), "haarcascades", None)
    if cascade_classifier is not None and haar_path:
        face_cascade = cascade_classifier(
            haar_path + "haarcascade_frontalface_default.xml"
        )
    else:
        face_cascade = None
except Exception as e:
    print(f"Error al cargar el detector facial: {e}")
    face_cascade = None


def _detectar_rostros(img_np: np.ndarray) -> List[Dict[str, Any]]:
    if face_cascade is None or face_cascade.empty():
        return []

    try:
        gray = cv2.cvtColor(img_np, cv2.COLOR_RGB2GRAY)
        gray = cv2.equalizeHist(gray)
        rostros = face_cascade.detectMultiScale(
            gray,
            scaleFactor=1.1,
            minNeighbors=5,
            minSize=(40, 40),
        )

        detecciones = []
        for (x, y, w, h) in rostros:
            detecciones.append({
                "tipo": "person",
                "confianza": 0.88,
                "etiqueta": "person",
                "coordenadas": {
                    "x1": int(x),
                    "y1": int(y),
                    "x2": int(x + w),
                    "y2": int(y + h),
                }
            })
        return detecciones
    except Exception:
        return []


def procesar_imagen(imagen_base64: str) -> Dict[str, Any]:
    """
    Procesa una imagen en codificación base64 usando YOLOv8 para detectar objetos.
    Devuelve un diccionario con los resultados de detección.
    """
    try:
        # Decodificar la imagen base64
        # Eliminar el encabezado si está presente (ej: "data:image/jpeg;base64,")
        if "," in imagen_base64:
            imagen_base64 = imagen_base64.split(",")[1]
        
        datos_imagen = base64.b64decode(imagen_base64)
        imagen = Image.open(io.BytesIO(datos_imagen))

        # Convertir la imagen a formato compatible con YOLO
        # YOLO espera imágenes en formato RGB como array de numpy
        img_np = np.array(imagen.convert("RGB"))

        detecciones = []
        modelo_yolo = _obtener_modelo()
        if modelo_yolo is not None:
            resultados = modelo_yolo(img_np)
            nombres_clases = modelo_yolo.names

            for result in resultados:
                boxes = result.boxes
                for box in boxes:
                    clase_id = int(box.cls[0])
                    confianza = float(box.conf[0])
                    x1, y1, x2, y2 = map(int, box.xyxy[0])

                    deteccion = {
                        "tipo": nombres_clases.get(clase_id, f"clase_{clase_id}"),
                        "confianza": confianza,
                        "etiqueta": nombres_clases.get(clase_id, f"clase_{clase_id}"),
                        "coordenadas": {
                            "x1": x1, "y1": y1, "x2": x2, "y2": y2
                        }
                    }
                    detecciones.append(deteccion)

        tiene_persona = any(
            str(d.get("tipo") or d.get("etiqueta") or "").lower() in {"person", "persona"}
            for d in detecciones
        )

        if not tiene_persona:
            detecciones.extend(_detectar_rostros(img_np))

        return {
            "exito": True,
            "detecciones": detecciones
        }

    except Exception as e:
        return {
            "exito": False,
            "error": f"Error al procesar la imagen: {str(e)}",
            "detecciones": []
        }


def autorizar_acceso(placa_vehiculo: str, id_usuario: int) -> Dict[str, Any]:
    """
    Simula la comprobación de autorización de acceso para un vehículo
    """
    # En una implementación real, consultarías la base de datos aquí
    return {
        "autorizado": True,
        "razon": "Vehículo registrado al usuario",
        "id_acceso": str(uuid.uuid4())
    }


def calcular_area_coordenadas(ancho_imagen: int, alto_imagen: int, 
                                x1: int, y1: int, x2: int, y2: int) -> Dict[str, float]:
    """
    Calcula el área de un objeto detectado en la imagen y las coordenadas normalizadas
    """
    ancho = x2 - x1
    alto = y2 - y1
    area = ancho * alto
    centro_x = (x1 + x2) / 2
    centro_y = (y1 + y2) / 2

    return {
        "area_px": area,
        "centro_x": centro_x,
        "centro_y": centro_y,
        "x_normalizado": centro_x / ancho_imagen if ancho_imagen > 0 else 0,
        "y_normalizado": centro_y / alto_imagen if alto_imagen > 0 else 0
    }


def detectar_anomalias(registros_acceso: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    """
    Simula la detección de anomalías en los registros de acceso
    """
    anomalias = []
    # En una implementación real, analizarías patrones sospechosos
    for registro in registros_acceso:
        # Marcador de posición para la lógica de detección de anomalías
        if not registro.get("esta_autorizado", False):
            anomalias.append({
                "tipo": "acceso_no_autorizado",
                "registro": registro,
                "gravedad": "alta"
            })
    return anomalias
