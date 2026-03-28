from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Acceso, Persona, Puerta, Dispositivo
from pydantic import BaseModel
from typing import Optional

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
def get_all(db: Session = Depends(get_db)):
    results = db.query(Acceso, Persona).join(Persona, Acceso.id_persona == Persona.id).all()
    output = []
    for access, persona in results:
        output.append({
            "id": access.id,
            "user_id": access.id_persona,
            "user_name": f"{persona.nombre} {persona.apellidos}",
            "vehicle_plate": "N/A",
            "access_time": access.fecha_hora.isoformat(),
            "access_type": "ENTRY" if access.tipo_acceso == 'P' else "EXIT",
            "is_authorized": True if access.resultado == 'p' else False
        })
    return output

@router.post("/")
def create(data: AccesoCreate, db: Session = Depends(get_db)):
    nuevo = Acceso(**data.model_dump())
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
    return nuevo
