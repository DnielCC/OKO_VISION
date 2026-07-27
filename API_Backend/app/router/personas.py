from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Persona, Usuario
from pydantic import BaseModel
from typing import Optional
from app.security.auth import obtener_usuario_actual

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
def obtener_todos(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
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
def obtener_uno(persona_id: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
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
def crear(datos: PersonaCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    nueva = Persona(**datos.model_dump())
    db.add(nueva)
    db.commit()
    db.refresh(nueva)
    return nueva

@router.put("/{persona_id}")
def actualizar(persona_id: int, datos: PersonaCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    persona = db.query(Persona).filter(Persona.id == persona_id).first()
    if not persona:
        raise HTTPException(status_code=404, detail="Persona no encontrada")

    for clave, valor in datos.model_dump().items():
        setattr(persona, clave, valor)

    db.commit()
    return persona

@router.patch("/{persona_id}")
def parchear(persona_id: int, datos: PersonaUpdate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    persona = db.query(Persona).filter(Persona.id == persona_id).first()
    if not persona:
        raise HTTPException(status_code=404, detail="Persona no encontrada")

    datos_actualizacion = datos.model_dump(exclude_unset=True)
    
    # Convertir fecha si viene en el parche
    if "fecha_nacimiento" in datos_actualizacion and datos_actualizacion["fecha_nacimiento"]:
        try:
            from datetime import datetime
            datos_actualizacion["fecha_nacimiento"] = datetime.strptime(datos_actualizacion["fecha_nacimiento"], "%Y-%m-%d").date()
        except Exception:
            pass

    for clave, valor in datos_actualizacion.items():
        setattr(persona, clave, valor)

    db.commit()
    db.refresh(persona)
    return persona

@router.delete("/{persona_id}")
def eliminar(persona_id: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    persona = db.query(Persona).filter(Persona.id == persona_id).first()
    if not persona:
        raise HTTPException(status_code=404, detail="Persona no encontrada")

    db.delete(persona)
    db.commit()
    return {"msg": "Persona eliminada"}
