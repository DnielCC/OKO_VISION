from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Usuario, Persona
from app.security.auth import verificar_contraseña, crear_token_acceso, obtener_usuario_actual
from pydantic import BaseModel, EmailStr, field_validator


class DatosInicioSesion(BaseModel):
    email: EmailStr
    password: str | None = None
    contraseña: str | None = None

    @field_validator("password", mode="before")
    @classmethod
    def _compat_password(cls, v):
        return v

    def get_password(self) -> str:
        pwd = self.password if self.password else self.contraseña
        if not pwd:
            raise ValueError("password / contraseña requerida")
        return pwd


router = APIRouter(prefix="/auth", tags=["Autenticación"])


def _build_user_response(usuario: Usuario, persona: Persona) -> dict:
    return {
        "id": usuario.id,
        "username": usuario.identificador,
        "email": persona.mail,
        "nombre": persona.nombre,
        "apellidos": persona.apellidos,
        "id_rol": usuario.id_rol,
        "id_persona": persona.id,
        "id_carrera": getattr(usuario, "id_carrera", None),
        "id_departamento": getattr(usuario, "id_departamento", None),
        "activo": bool(getattr(usuario, "activo", True)),
        "foto": getattr(persona, "foto", None),
    }


@router.get("/me", summary="Usuario actual")
def info_usuario_actual(
    db: Session = Depends(get_db),
    user: Usuario = Depends(obtener_usuario_actual),
):
    persona = db.query(Persona).filter(Persona.id == user.id_persona).first()
    if not persona:
        raise HTTPException(status_code=404, detail="Persona no encontrada")
    return _build_user_response(user, persona)


@router.post("/login", summary="Inicio de sesión")
def iniciar_sesion(datos: DatosInicioSesion, db: Session = Depends(get_db)):
    try:
        contraseña_plana = datos.get_password()
    except ValueError:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="El campo password (o contraseña) es requerido"
        )

    persona = db.query(Persona).filter(Persona.mail == datos.email).first()
    if not persona:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Credenciales incorrectas")

    usuario = db.query(Usuario).filter(Usuario.id_persona == persona.id).first()
    if not usuario:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Credenciales incorrectas")

    if not usuario.password or not verificar_contraseña(contraseña_plana, usuario.password):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Credenciales incorrectas")

    token_acceso = crear_token_acceso(datos={"sub": str(usuario.id)})

    user_info = _build_user_response(usuario, persona)
    return {
        "access_token": token_acceso,
        "token_type": "bearer",
        "id": usuario.id,
        "username": usuario.identificador,
        "email": persona.mail,
        "nombre": persona.nombre,
        "apellidos": persona.apellidos,
        "id_rol": usuario.id_rol,
        "id_persona": persona.id,
        "user": user_info,
        **user_info,
    }
