from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Usuario
from app.models.user import UsuarioCreate, UsuarioUpdate
from app.data.database import Usuario, Persona
from app.security.auth import get_password_hash

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
            "apellidos": persona.apellidos,
            "id_rol": user.id_rol,
            "id_persona": persona.id,
            "foto": persona.foto
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
    # Hashear contraseña antes de guardar
    user_data = data.model_dump()
    user_data["password"] = get_password_hash(user_data["password"])
    nuevo = Usuario(**user_data)
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
    return nuevo

@router.put("/{usuario_id}")
def update(usuario_id: int, data: UsuarioCreate, db: Session = Depends(get_db)):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="No encontrado")

    user_data = data.model_dump()
    if "password" in user_data and user_data["password"]:
        user_data["password"] = get_password_hash(user_data["password"])

    for key, value in user_data.items():
        setattr(usuario, key, value)

    db.commit()
    return usuario

@router.patch("/{usuario_id}")
def patch(usuario_id: int, data: UsuarioUpdate, db: Session = Depends(get_db)):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="No encontrado")

    update_data = data.model_dump(exclude_unset=True)
    if "password" in update_data and update_data["password"]:
        update_data["password"] = get_password_hash(update_data["password"])

    for key, value in update_data.items():
        setattr(usuario, key, value)

    db.commit()
    return usuario

@router.delete("/{usuario_id}")
def delete(
    usuario_id: int,
    db: Session = Depends(get_db)
):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="No encontrado")

    db.delete(usuario)
    db.commit()
    return {"msg": f"Usuario eliminado exitosamente"}