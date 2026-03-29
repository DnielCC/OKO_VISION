from pydantic import BaseModel, Field, field_validator
from typing import Optional
from datetime import datetime
import re

class VehiculoBase(BaseModel):
    marca: str = Field(..., min_length=2)
    modelo: str = Field(..., min_length=1)
    anio: Optional[int] = Field(None, ge=1900, le=2100)
    color: Optional[str] = None
    tipo: str = Field(..., pattern="^(auto|moto|camioneta|otro)$")
    
    @field_validator("marca")
    @classmethod
    def validar_marca(cls, v: str):
        v = v.strip()
        if not re.fullmatch(r"[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\- ]{2,100}", v):
            raise ValueError("Marca inválida")
        return v
    
    @field_validator("modelo")
    @classmethod
    def validar_modelo(cls, v: str):
        v = v.strip()
        if not re.fullmatch(r"[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\- ]{1,100}", v):
            raise ValueError("Modelo inválido")
        return v
    
    @field_validator("anio")
    @classmethod
    def validar_anio(cls, v: Optional[int]):
        if v is None:
            return v
        limite = datetime.now().year + 1
        if v < 1900 or v > limite:
            raise ValueError("Año fuera de rango")
        return v
    
    @field_validator("color")
    @classmethod
    def validar_color(cls, v: Optional[str]):
        if v is None or v.strip() == "":
            return None
        v = v.strip()
        if not re.fullmatch(r"[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]{3,50}", v):
            raise ValueError("Color inválido")
        return v
    
    @field_validator("tipo")
    @classmethod
    def normalizar_tipo(cls, v: str):
        return v.strip().lower()

class VehiculoCreate(VehiculoBase):
    owner_id: int

class VehiculoUpdate(BaseModel):
    marca: Optional[str] = None
    modelo: Optional[str] = None
    anio: Optional[int] = None
    color: Optional[str] = None
    tipo: Optional[str] = None
    
    @field_validator("marca")
    @classmethod
    def v_marca(cls, v: Optional[str]):
        if v is None:
            return v
        v = v.strip()
        if not re.fullmatch(r"[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\- ]{2,100}", v):
            raise ValueError("Marca inválida")
        return v
    
    @field_validator("modelo")
    @classmethod
    def v_modelo(cls, v: Optional[str]):
        if v is None:
            return v
        v = v.strip()
        if not re.fullmatch(r"[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\- ]{1,100}", v):
            raise ValueError("Modelo inválido")
        return v
    
    @field_validator("anio")
    @classmethod
    def v_anio(cls, v: Optional[int]):
        if v is None:
            return v
        limite = datetime.now().year + 1
        if v < 1900 or v > limite:
            raise ValueError("Año fuera de rango")
        return v
    
    @field_validator("color")
    @classmethod
    def v_color(cls, v: Optional[str]):
        if v is None or v.strip() == "":
            return None
        v = v.strip()
        if not re.fullmatch(r"[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]{3,50}", v):
            raise ValueError("Color inválido")
        return v
    
    @field_validator("tipo")
    @classmethod
    def v_tipo(cls, v: Optional[str]):
        if v is None:
            return v
        v = v.strip().lower()
        if v not in {"auto", "moto", "camioneta", "otro"}:
            raise ValueError("Tipo inválido")
        return v

class VehiculoOut(VehiculoBase):
    id: int

    class Config:
        from_attributes = True
