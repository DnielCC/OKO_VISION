from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Usuario
from app.models.user import UsuarioCreate, UsuarioUpdate
from app.data.database import Usuario, Persona
from app.security.auth import verify_user

router = APIRouter(prefix="/usuarios", tags=["Usuarios"])

@router.get("/")
def get_all(db: Session = Depends(get_db)):
    # Unimos con Persona para obtener el email y nombre para el portal de usuario
    results = db.query(Usuario, Persona).join(Persona, Usuario.id_persona == Persona.id).all()
    output = []
    for user, persona in results:
        output.append({
            "id": user.id,
            "username": user.identificador,
            "email": persona.mail,
            "nombre": persona.nombre,
            "apellidos": persona.apellidos
        })
    return output

@router.get("/{usuario_id}")
def get_one(usuario_id: int, db: Session = Depends(get_db)):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    return usuario

@router.post("/")
def create(data: UsuarioCreate, db: Session = Depends(get_db)):
    nuevo = Usuario(**data.model_dump())
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
    return nuevo

@router.put("/{usuario_id}")
def update(usuario_id: int, data: UsuarioCreate, db: Session = Depends(get_db)):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="No encontrado")

    for key, value in data.model_dump().items():
        setattr(usuario, key, value)

    db.commit()
    return usuario

@router.patch("/{usuario_id}")
def patch(usuario_id: int, data: UsuarioUpdate, db: Session = Depends(get_db)):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="No encontrado")

    for key, value in data.model_dump(exclude_unset=True).items():
        setattr(usuario, key, value)

    db.commit()
    return usuario

@router.delete("/{usuario_id}")
def delete(
    usuario_id: int,
    db: Session = Depends(get_db),
    user: str = Depends(verify_user)
):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="No encontrado")

    db.delete(usuario)
    db.commit()
    return {"msg": f"Usuario eliminado por {user}"}