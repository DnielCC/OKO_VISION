from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from typing import List, Optional
from sqlalchemy.orm import Session
from app.security.auth import obtener_usuario_actual
from app.vision import procesar_imagen, autorizar_acceso, calcular_area_coordenadas, detectar_anomalias
from app.data.database import Usuario
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
def api_procesar_imagen(datos: EntradaImagen, db: Session = Depends(get_db)):
    """
    Endpoint para procesar una imagen y detectar objetos (vehículos, etc.) usando YOLOv8.
    Requiere autenticación.
    """
    try:
        resultado = procesar_imagen(datos.imagen_base64)
        if not resultado["exito"]:
            raise HTTPException(status_code=500, detail=resultado.get("error", "Error desconocido"))
        detecciones = resultado.get("detecciones", [])
        resumen = {
            "personas": 0,
            "vehiculos": 0,
            "otros": 0,
        }

        for deteccion in detecciones:
            etiqueta = str(deteccion.get("tipo") or deteccion.get("etiqueta") or "").lower()
            if etiqueta in {"person", "persona"}:
                resumen["personas"] += 1
            elif etiqueta in {"car", "truck", "bus", "motorcycle", "bicycle", "vehicle", "vehiculo", "vehículo"}:
                resumen["vehiculos"] += 1
            else:
                resumen["otros"] += 1

        resultado["resumen"] = resumen
        resultado["timestamp"] = datetime.datetime.utcnow().isoformat()
        return resultado
    except Exception as e:
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
