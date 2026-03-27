from pydantic import BaseModel, EmailStr, Field
from datetime import date
from typing import Optional

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
    pass

class UsuarioUpdate(BaseModel):
    identificador: Optional[str]

class UsuarioOut(UsuarioBase):
    id: int

    class Config:
        from_attributes = True