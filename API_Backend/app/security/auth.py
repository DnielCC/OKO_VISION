from fastapi import Depends, HTTPException, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
import bcrypt
from jose import JWTError, jwt
from datetime import datetime, timedelta
from sqlalchemy.orm import Session
from pydantic_settings import BaseSettings
from app.data.db import get_db
from app.data.database import Usuario, Persona


class Settings(BaseSettings):
    secret_key: str = "demo-secret-key-change-in-production"
    algorithm: str = "HS256"
    access_token_expire_minutes: int = 30

    class Config:
        env_file = ".env"


# Configuración JWT
settings = Settings()
SECRET_KEY = settings.secret_key
ALGORITHM = settings.algorithm
ACCESS_TOKEN_EXPIRE_MINUTES = settings.access_token_expire_minutes

# Esquema de seguridad para JWT (con auto_error=False para manejar 401 nosotros)
security = HTTPBearer(auto_error=False)

def verificar_contraseña(contraseña_plana, contraseña_encriptada):
    """Verifica que la contraseña plana coincida con la encriptada.
    Devuelve False si el hash guardado no es un bcrypt válido (en lugar de
    explotar con ValueError: Invalid salt) para evitar 500 en login."""
    if not isinstance(contraseña_plana, str) or not isinstance(contraseña_encriptada, str):
        return False
    contraseña_encriptada = contraseña_encriptada.strip()
    if not contraseña_encriptada:
        return False
    # bcrypt salts válidos empiezan por $2a$, $2b$, $2y$ y miden al menos 60 chars
    if not (
        len(contraseña_encriptada) >= 60
        and contraseña_encriptada.startswith(('$2a$', '$2b$', '$2y$'))
    ):
        return False
    try:
        contraseña_truncada = contraseña_plana[:72]
        return bool(bcrypt.checkpw(
            contraseña_truncada.encode('utf-8'),
            contraseña_encriptada.encode('utf-8'),
        ))
    except (ValueError, TypeError):
        return False

def obtener_hash_contraseña(contraseña):
    """Genera el hash bcrypt de una contraseña"""
    # Truncar a 72 bytes como recomienda bcrypt
    contraseña_truncada = contraseña[:72]
    # Generar salt y hash
    salt = bcrypt.gensalt()
    return bcrypt.hashpw(contraseña_truncada.encode('utf-8'), salt).decode('utf-8')

def crear_token_acceso(datos: dict):
    """Crea un token de acceso JWT con los datos proporcionados"""
    a_codificar = datos.copy()
    expiracion = datetime.utcnow() + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    a_codificar.update({"exp": expiracion})
    token_jwt = jwt.encode(a_codificar, SECRET_KEY, algorithm=ALGORITHM)
    return token_jwt

def obtener_usuario_actual(
    credenciales: HTTPAuthorizationCredentials = Depends(security),
    db: Session = Depends(get_db)
):
    """
    Obtiene el usuario actual a partir del token JWT.
    Lanza 401 si el token es inválido o el usuario no existe.
    """
    excepcion_credenciales = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="No se pudieron validar las credenciales",
        headers={"WWW-Authenticate": "Bearer"},
    )
    if not credenciales or not credenciales.credentials:
        raise excepcion_credenciales
    
    try:
        payload = jwt.decode(credenciales.credentials, SECRET_KEY, algorithms=[ALGORITHM])
        usuario_id_str: str = payload.get("sub")
        if usuario_id_str is None:
            raise excepcion_credenciales
        usuario_id: int = int(usuario_id_str)
    except JWTError:
        raise excepcion_credenciales
    except ValueError:
        # Si no se puede convertir a int, credenciales inválidas
        raise excepcion_credenciales
    
    usuario = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if usuario is None:
        raise excepcion_credenciales
    return usuario