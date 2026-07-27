from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Acceso, Persona, Puerta, Dispositivo, Usuario
from pydantic import BaseModel
from typing import Optional
from app.security.auth import obtener_usuario_actual

class AccesoCreate(BaseModel):
    id_persona: int
    id_vehiculo: Optional[int] = None
    id_puerta: int
    id_dispositivo: int
    tipo_acceso: str
    resultado: str
    metodo: str
    autoriza: Optional[int] = None

router = APIRouter(prefix="/accesos", tags=["Accesos"])

@router.get("/")
def obtener_todos(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    resultados = db.query(Acceso, Persona).join(Persona, Acceso.id_persona == Persona.id).all()
    salida = []
    for acceso, persona in resultados:
        salida.append({
            "id": acceso.id,
            "user_id": acceso.id_persona,
            "user_name": f"{persona.nombre} {persona.apellidos}",
            "vehicle_plate": "N/A",
            "access_time": acceso.fecha_hora.isoformat(),
            "access_type": "ENTRY" if acceso.tipo_acceso == 'P' else "EXIT",
            "is_authorized": True if acceso.resultado == 'p' else False
        })
    return salida

@router.post("/")
def crear(datos: AccesoCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    nuevo = Acceso(**datos.model_dump())
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
    return nuevo
