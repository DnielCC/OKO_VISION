from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Vehiculo, PersonaVehiculo, Usuario
from app.models.cars import VehiculoCreate, VehiculoUpdate

car = APIRouter(prefix="/vehiculos", tags=["Vehiculos"])

@car.get("/")
def get_all(db: Session = Depends(get_db)):
    # Unimos con PersonaVehiculo y Usuario para obtener el owner_id (id del usuario)
    results = db.query(Vehiculo, PersonaVehiculo, Usuario).join(
        PersonaVehiculo, Vehiculo.id == PersonaVehiculo.id_vehiculo
    ).join(
        Usuario, PersonaVehiculo.id_persona == Usuario.id_persona
    ).distinct().all()
    
    output = []
    for v, pv, u in results:
        output.append({
            "id": v.id,
            "plate": "N/A", # La nueva estructura no parece tener placa en Vehiculo
            "brand": v.marca,
            "model": v.modelo,
            "color": v.color,
            "owner_id": u.id
        })
    return output

@car.get("/{vehiculo_id}")
def get_one(vehiculo_id: int, db: Session = Depends(get_db)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="Vehiculo no encontrado")
    return vehiculo

@car.post("/")
def create(data: VehiculoCreate, db: Session = Depends(get_db)):
    nuevo = Vehiculo(**data.model_dump())
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
    return nuevo

@car.put("/{vehiculo_id}")
def update(vehiculo_id: int, data: VehiculoCreate, db: Session = Depends(get_db)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="No encontrado")

    for key, value in data.model_dump().items():
        setattr(vehiculo, key, value)

    db.commit()
    return vehiculo

@car.patch("/{vehiculo_id}")
def patch(vehiculo_id: int, data: VehiculoUpdate, db: Session = Depends(get_db)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="No encontrado")

    for key, value in data.model_dump(exclude_unset=True).items():
        setattr(vehiculo, key, value)

    db.commit()
    return vehiculo

@car.delete("/{vehiculo_id}")
def delete(vehiculo_id: int, db: Session = Depends(get_db)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="No encontrado")

    db.delete(vehiculo)
    db.commit()
    return {"msg": "Vehiculo eliminado"}