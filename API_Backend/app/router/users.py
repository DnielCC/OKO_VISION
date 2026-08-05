from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Usuario
from app.models.user import UsuarioCreate, UsuarioUpdate
from app.data.database import Usuario, Persona
from app.security.auth import obtener_hash_contraseña, obtener_usuario_actual

router = APIRouter(prefix="/usuarios", tags=["Usuarios"])


@router.get("/me")
def obtener_yo(
    db: Session = Depends(get_db),
    usuario_actual: Usuario = Depends(obtener_usuario_actual),
):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_actual.id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    persona = db.query(Persona).filter(Persona.id == usuario.id_persona).first()
    base = {
        "id": usuario.id,
        "username": usuario.identificador,
        "email": persona.mail if persona else None,
        "nombre": persona.nombre if persona else None,
        "apellidos": persona.apellidos if persona else None,
        "id_rol": usuario.id_rol,
        "id_persona": usuario.id_persona,
        "id_carrera": getattr(usuario, "id_carrera", None),
        "id_departamento": getattr(usuario, "id_departamento", None),
        "activo": bool(getattr(usuario, "activo", True)),
        "foto": getattr(persona, "foto", None) if persona else None,
    }
    return base


@router.get("")
def obtener_todos(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    # Unimos con Persona para obtener el email y nombre para el portal de usuario
    resultados = db.query(Usuario, Persona).join(Persona, Usuario.id_persona == Persona.id).all()
    salida = []
    for usuario, persona in resultados:
        salida.append({
            "id": usuario.id,
            "username": usuario.identificador,
            "email": persona.mail,
            "nombre": persona.nombre,
            "apellidos": persona.apellidos,
            "id_rol": usuario.id_rol,
            "id_persona": persona.id,
            "foto": persona.foto
        })
    return salida

@router.get("/{usuario_id}")
def obtener_uno(usuario_id: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    return usuario

@router.post("")
def crear(datos: UsuarioCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    # Hashear contraseña antes de guardar
    datos_usuario = datos.model_dump()
    datos_usuario["password"] = obtener_hash_contraseña(datos_usuario["password"])
    nuevo = Usuario(**datos_usuario)
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
    return nuevo

@router.put("/{usuario_id}")
def actualizar(usuario_id: int, datos: UsuarioCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="No encontrado")

    datos_usuario = datos.model_dump()
    if "password" in datos_usuario and datos_usuario["password"]:
        datos_usuario["password"] = obtener_hash_contraseña(datos_usuario["password"])

    for clave, valor in datos_usuario.items():
        setattr(usuario, clave, valor)

    db.commit()
    return usuario

@router.patch("/{usuario_id}")
def parchear(usuario_id: int, datos: UsuarioUpdate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="No encontrado")

    datos_actualizacion = datos.model_dump(exclude_unset=True)
    if "password" in datos_actualizacion and datos_actualizacion["password"]:
        datos_actualizacion["password"] = obtener_hash_contraseña(datos_actualizacion["password"])

    for clave, valor in datos_actualizacion.items():
        setattr(usuario, clave, valor)

    db.commit()
    return usuario

@router.delete("/{usuario_id}")
def eliminar(
    usuario_id: int,
    db: Session = Depends(get_db),
    usuario_actual: Usuario = Depends(obtener_usuario_actual)
):
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="No encontrado")

    db.delete(usuario)
    db.commit()
    return {"msg": f"Usuario eliminado exitosamente"}