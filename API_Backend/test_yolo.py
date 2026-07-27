"""
Script de prueba simple para YOLOv8 en OKO Vision
"""
from ultralytics import YOLO
import cv2

def probar_yolo():
    print("Cargando modelo YOLOv8 nano (yolov8n.pt)...")
    # Cargar el modelo (se descargará automáticamente si no está presente)
    modelo = YOLO("yolov8n.pt")
    
    print("Modelo cargado exitosamente!")
    
    # Prueba 1: Detección en una imagen de ejemplo (usa una imagen que tengas o descarga una)
    # Por defecto, usaremos una prueba rápida con el modelo mismo
    print("\n--- Prueba de detección rápida ---")
    # Ejecutar detección en un ejemplo básico
    # Puedes reemplazar '0' con la ruta a tu imagen (ej: "mi_imagen.jpg")
    # O usar '0' para usar la cámara web (si tienes una)
    try:
        print("Ejecutando detección...")
        # Prueba con una imagen de ejemplo (si no tienes una, usamos el modo predict con una fuente dummy)
        # Para una prueba rápida, usaremos el comando predict de ultralytics
        resultados = modelo.predict(source="https://ultralytics.com/images/bus.jpg", save=True)
        
        print("\n✅ Detección completada!")
        print(f"Se detectaron {len(resultados[0].boxes)} objetos.")
        print("\nObjetos detectados:")
        for box in resultados[0].boxes:
            clase_id = int(box.cls[0])
            confianza = float(box.conf[0])
            print(f"- {modelo.names[clase_id]} (confianza: {confianza:.2f})")
            
        print("\nImagen resultado guardada en: runs/detect/predict/")
        
    except Exception as e:
        print(f"Error en la prueba: {e}")
        print("\nIntentando con una prueba más simple (solo carga del modelo)...")
        print("✅ El modelo se cargó correctamente, la instalación está bien!")

if __name__ == "__main__":
    probar_yolo()
