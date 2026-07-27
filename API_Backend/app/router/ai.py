from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from typing import List, Optional
from sqlalchemy.orm import Session
from app.security.auth import obtener_usuario_actual
from app.vision import procesar_imagen, autorizar_acceso, calcular_area_coordenadas, detectar_anomalias
from app.data.database import Usuario, LaravelAccessLog, LaravelAlert, LaravelVehicle
from app.data.db import get_db
import datetime

router = APIRouter(prefix="/ai", tags=["IA y Visión por Computadora"])

class EntradaImagen(BaseModel):
    imagen_base64: str

class EntradaArea(BaseModel):
    ancho_imagen: Optional[int] = None
    alto_imagen: Optional[int] = None
    x1: int
    y1: int
    x2: int
    y2: int

class RegistroAcceso(BaseModel):
    id_usuario: int
    placa_vehiculo: str
    hora_acceso: str
    esta_autorizado: bool

@router.post("/procesar-imagen")
def api_procesar_imagen(datos: EntradaImagen, usuario_actual: Usuario = Depends(obtener_usuario_actual), db: Session = Depends(get_db)):
    """
    Endpoint para procesar una imagen y detectar objetos (vehículos, etc.) usando YOLOv8.
    Requiere autenticación.
    """
    try:
        resultado = procesar_imagen(datos.imagen_base64)
        if not resultado["exito"]:
            raise HTTPException(status_code=500, detail=resultado.get("error", "Error desconocido"))
        
        detecciones = resultado.get("detecciones", [])
        
        # Procesar cada detección de vehículo
        for deteccion in detecciones:
            etiqueta = deteccion.get("tipo") or deteccion.get("etiqueta") or ""
            etiqueta_lower = etiqueta.lower()
            if etiqueta_lower in ["car", "truck", "bus", "motorcycle", "bicycle", "vehicle", "vehiculo"]:
                # Simular detección de placa (podemos usar una placa inventada para testing por ahora
                # En un sistema real, esto vendría de un sistema OCR de placa
                placa = "ABC-" + str(datetime.datetime.utcnow().strftime("%Y%m%d%H%M%S")[-6:])
                
                # Verificar si el vehículo está registrado
                vehiculo_registrado = db.query(LaravelVehicle).filter(LaravelVehicle.plate == placa).first()
                autorizado = vehiculo_registrado is not None
                
                # Guardar registro de acceso
                # Usar user_id 1 por ahora, que es el admin de Laravel (admin@okovision.com
                nuevo_acceso = LaravelAccessLog(
                    user_id=1,
                    vehicle_plate=placa,
                    access_time=datetime.datetime.utcnow(),
                    access_type="ENTRY",
                    is_authorized=autorizado
                )
                db.add(nuevo_acceso)
                db.commit()
                db.refresh(nuevo_acceso)
                
                # Si no está autorizado, crear alerta
                if not autorizado:
                    nueva_alerta = LaravelAlert(
                    title="Acceso no autorizado",
                    description=f"Vehículo con placa {placa} intentó acceder sin autorización",
                    severity="CRITICAL",
                    created_at=datetime.datetime.utcnow(),
                    is_resolved=False
                )
                    db.add(nueva_alerta)
                    db.commit()
        
        return resultado
    except Exception as e:
        db.rollback()
        raise HTTPException(status_code=500, detail=f"Error al procesar la imagen: {str(e)}")

@router.post("/autorizar-acceso")
def api_autorizar_acceso(placa: str, id_usuario: int, usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    """
    Endpoint para autorizar acceso de un vehículo
    Requiere autenticación
    """
    try:
        resultado = autorizar_acceso(placa, id_usuario)
        return resultado
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error en la autorización: {str(e)}")

@router.post("/calcular-area")
def api_calcular_area(datos: EntradaArea, ancho_imagen: Optional[int] = None, alto_imagen: Optional[int] = None, usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    """
    Endpoint para calcular el área de un objeto en la imagen y obtener las coordenadas normalizadas.
    Requiere autenticación.
    """
    try:
        # Usar valores de los parámetros o del body
        final_ancho = datos.ancho_imagen if datos.ancho_imagen else ancho_imagen
        final_alto = datos.alto_imagen if datos.alto_imagen else alto_imagen

        if not final_ancho or not final_alto:
            raise HTTPException(status_code=400, detail="Debes proporcionar ancho_imagen y alto_imagen (en el body o como parámetros)")

        resultado = calcular_area_coordenadas(
            final_ancho, final_alto,
            datos.x1, datos.y1, datos.x2, datos.y2
        )
        return resultado
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error al calcular el área: {str(e)}")

@router.post("/detectar-anomalias")
def api_detectar_anomalias(registros: List[RegistroAcceso], usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    """
    Endpoint para detectar anomalías en los registros de acceso
    Requiere autenticación
    """
    try:
        dicts_registros = [registro.model_dump() for registro in registros]
        resultado = detectar_anomalias(dicts_registros)
        return {
            "anomalias_encontradas": len(resultado),
            "anomalias": resultado
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error al detectar anomalías: {str(e)}")
