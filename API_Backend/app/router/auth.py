from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Usuario, Persona
from app.security.auth import verificar_contraseña, crear_token_acceso
from pydantic import BaseModel, EmailStr

class DatosInicioSesion(BaseModel):
    email: EmailStr
    contraseña: str

router = APIRouter(prefix="/auth", tags=["Autenticación"])

@router.post("/login", summary="Inicio de sesión", description="Permite a un usuario iniciar sesión con su correo electrónico y contraseña, devolviendo un token JWT válido para acceder a endpoints protegidos")
def iniciar_sesion(datos: DatosInicioSesion, db: Session = Depends(get_db)):
    """
    Endpoint para iniciar sesión.
    Devuelve un token JWT y la información del usuario.
    """
    # Buscar a la persona por email
    persona = db.query(Persona).filter(Persona.mail == datos.email).first()
    
    if not persona:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales incorrectas"
        )
        
    # Buscar el usuario asociado
    usuario = db.query(Usuario).filter(Usuario.id_persona == persona.id).first()
    
    if not usuario:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales incorrectas"
        )
        
    # Verificar contraseña
    if not usuario.password or not verificar_contraseña(datos.contraseña, usuario.password):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales incorrectas"
        )
    
    # Crear token de acceso (convertir id a string para JWT)
    token_acceso = crear_token_acceso(datos={"sub": str(usuario.id)})
        
    # Login exitoso, retornar info y token
    return {
        "access_token": token_acceso,
        "token_type": "bearer",
        "id": usuario.id,
        "username": usuario.identificador,
        "email": persona.mail,
        "nombre": persona.nombre,
        "apellidos": persona.apellidos,
        "id_rol": usuario.id_rol,
        "id_persona": persona.id
    }
