from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Usuario, Persona
from app.security.auth import verify_password, create_access_token
from pydantic import BaseModel, EmailStr

class LoginData(BaseModel):
    email: EmailStr
    password: str

router = APIRouter(prefix="/auth", tags=["Auth"])

@router.post("/login")
def login(data: LoginData, db: Session = Depends(get_db)):
    # Buscar a la persona por email
    persona = db.query(Persona).filter(Persona.mail == data.email).first()
    
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
    if not usuario.password or not verify_password(data.password, usuario.password):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales incorrectas"
        )
    
    # Crear token de acceso
    access_token = create_access_token(data={"sub": usuario.id})
        
    # Login exitoso, retornar info y token
    return {
        "access_token": access_token,
        "token_type": "bearer",
        "id": usuario.id,
        "username": usuario.identificador,
        "email": persona.mail,
        "nombre": persona.nombre,
        "apellidos": persona.apellidos,
        "id_rol": usuario.id_rol,
        "id_persona": persona.id
    }
