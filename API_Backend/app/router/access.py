from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Acceso, Persona

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
