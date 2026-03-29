from pydantic import BaseModel, EmailStr, Field, field_validator
from datetime import date
from typing import Optional
import re

class PersonaBase(BaseModel):
    nombre: str = Field(..., min_length=2, max_length=200)
    apellidos: str = Field(..., min_length=2, max_length=300)
    fecha_nacimiento: Optional[date]
    sexo: Optional[str] = Field(None, pattern="^(H|M)$")
    telefono: Optional[str] = Field(None, min_length=10, max_length=10)
    mail: Optional[EmailStr]

class UsuarioBase(BaseModel):
    id_persona: int
    id_rol: int
    identificador: str = Field(..., min_length=3, max_length=15)

class UsuarioCreate(UsuarioBase):
    password: str = Field(..., min_length=8, max_length=64)

    @field_validator("password")
    @classmethod
    def validar_pwd_create(cls, v: str):
        if any(c.isspace() for c in v):
            raise ValueError("La contraseña no puede contener espacios")
        if not re.search(r"[A-Za-z]", v) or not re.search(r"\d", v):
            raise ValueError("La contraseña debe incluir letras y números")
        comunes = {"12345678", "password", "password123", "qwerty", "abc123", "11111111", "123456789"}
        if v.lower() in comunes:
            raise ValueError("La contraseña es demasiado común")
        return v

class UsuarioUpdate(BaseModel):
    id_persona: Optional[int] = None
    id_rol: Optional[int] = None
    identificador: Optional[str] = None
    password: Optional[str] = None

    @field_validator("password")
    @classmethod
    def validar_pwd_update(cls, v: Optional[str]):
        if v is None:
            return v
        if len(v) < 8 or len(v) > 64:
            raise ValueError("La contraseña debe tener entre 8 y 64 caracteres")
        if any(c.isspace() for c in v):
            raise ValueError("La contraseña no puede contener espacios")
        if not re.search(r"[A-Za-z]", v) or not re.search(r"\d", v):
            raise ValueError("La contraseña debe incluir letras y números")
        comunes = {"12345678", "password", "password123", "qwerty", "abc123", "11111111", "123456789"}
        if v.lower() in comunes:
            raise ValueError("La contraseña es demasiado común")
        return v

class UsuarioOut(UsuarioBase):
    id: int

    class Config:
        from_attributes = True
