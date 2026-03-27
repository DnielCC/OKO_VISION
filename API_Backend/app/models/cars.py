from pydantic import BaseModel, Field
from typing import Optional

class VehiculoBase(BaseModel):
    marca: str = Field(..., min_length=2)
    modelo: str = Field(..., min_length=1)
    anio: Optional[int] = Field(None, ge=1900, le=2100)
    color: Optional[str]
    tipo: str = Field(..., pattern="^(auto|moto|camioneta|otro)$")

class VehiculoCreate(VehiculoBase):
    pass

class VehiculoUpdate(BaseModel):
    marca: Optional[str]
    modelo: Optional[str]
    anio: Optional[int]
    color: Optional[str]
    tipo: Optional[str]

class VehiculoOut(VehiculoBase):
    id: int

    class Config:
        from_attributes = True