from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Persona
from pydantic import BaseModel
from typing import Optional

class PersonaBase(BaseModel):
    nombre: str
    apellidos: str
    fecha_nacimiento: Optional[str] = None
    sexo: Optional[str] = None
    foto: Optional[str] = None
    telefono: Optional[str] = None
    mail: Optional[str] = None

class PersonaCreate(PersonaBase):
    pass

class PersonaUpdate(BaseModel):
    nombre: Optional[str] = None
    apellidos: Optional[str] = None
    fecha_nacimiento: Optional[str] = None
    sexo: Optional[str] = None
    foto: Optional[str] = None
    telefono: Optional[str] = None
    mail: Optional[str] = None

router = APIRouter(prefix="/personas", tags=["Personas"])

@router.get("/")
def get_all(db: Session = Depends(get_db)):
    personas = db.query(Persona).all()
    return [
        {
            "id": p.id,
            "nombre": p.nombre,
            "apellidos": p.apellidos,
            "fecha_nacimiento": p.fecha_nacimiento.isoformat() if p.fecha_nacimiento else None,
            "sexo": p.sexo,
            "foto": p.foto,
            "telefono": p.telefono,
            "mail": p.mail
        }
        for p in personas
    ]

@router.get("/{persona_id}")
def get_one(persona_id: int, db: Session = Depends(get_db)):
    persona = db.query(Persona).filter(Persona.id == persona_id).first()
    if not persona:
        raise HTTPException(status_code=404, detail="Persona no encontrada")
    
    return {
        "id": persona.id,
        "nombre": persona.nombre,
        "apellidos": persona.apellidos,
        "fecha_nacimiento": persona.fecha_nacimiento.isoformat() if persona.fecha_nacimiento else None,
        "sexo": persona.sexo,
        "foto": persona.foto,
        "telefono": persona.telefono,
        "mail": persona.mail
    }

@router.post("/")
def create(data: PersonaCreate, db: Session = Depends(get_db)):
    nueva = Persona(**data.model_dump())
    db.add(nueva)
    db.commit()
    db.refresh(nueva)
    return nueva

@router.put("/{persona_id}")
def update(persona_id: int, data: PersonaCreate, db: Session = Depends(get_db)):
    persona = db.query(Persona).filter(Persona.id == persona_id).first()
    if not persona:
        raise HTTPException(status_code=404, detail="Persona no encontrada")

    for key, value in data.model_dump().items():
        setattr(persona, key, value)

    db.commit()
    return persona

@router.patch("/{persona_id}")
def patch(persona_id: int, data: PersonaUpdate, db: Session = Depends(get_db)):
    persona = db.query(Persona).filter(Persona.id == persona_id).first()
    if not persona:
        raise HTTPException(status_code=404, detail="Persona no encontrada")

    for key, value in data.model_dump(exclude_unset=True).items():
        setattr(persona, key, value)

    db.commit()
    return persona

@router.delete("/{persona_id}")
def delete(persona_id: int, db: Session = Depends(get_db)):
    persona = db.query(Persona).filter(Persona.id == persona_id).first()
    if not persona:
        raise HTTPException(status_code=404, detail="Persona no encontrada")

    db.delete(persona)
    db.commit()
    return {"msg": "Persona eliminada"}
