from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Vehiculo, PersonaVehiculo, Usuario, Estatus
from app.models.cars import VehiculoCreate, VehiculoUpdate

car = APIRouter(prefix="/vehiculos", tags=["Vehiculos"])

@car.get("/")
def get_all(db: Session = Depends(get_db)):
    results = db.query(Vehiculo, PersonaVehiculo, Usuario).join(
        PersonaVehiculo, Vehiculo.id == PersonaVehiculo.id_vehiculo
    ).join(
        Usuario, PersonaVehiculo.id_persona == Usuario.id_persona
    ).distinct().all()
    
    output = []
    for v, pv, u in results:
        output.append({
            "id": v.id,
            "marca": v.marca,
            "modelo": v.modelo,
            "anio": v.anio,
            "color": v.color,
            "tipo": v.tipo,
            "owner_id": u.id
        })
    return output

@car.get("/{vehiculo_id}")
def get_one(vehiculo_id: int, db: Session = Depends(get_db)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="Vehiculo no encontrado")
    return vehiculo

@car.post("/", status_code=status.HTTP_201_CREATED)
def create(data: VehiculoCreate, db: Session = Depends(get_db)):
    usuario = db.query(Usuario).filter(Usuario.id == data.owner_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    count = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_persona == usuario.id_persona).count()
    if count >= 2:
        raise HTTPException(status_code=400, detail="El usuario ya tiene el máximo de 2 vehículos")
    dup = db.query(Vehiculo, PersonaVehiculo).join(PersonaVehiculo, Vehiculo.id == PersonaVehiculo.id_vehiculo)\
        .filter(PersonaVehiculo.id_persona == usuario.id_persona, Vehiculo.marca == data.marca, Vehiculo.modelo == data.modelo, Vehiculo.anio == data.anio).first()
    if dup:
        raise HTTPException(status_code=400, detail="Ya tienes un vehículo con la misma marca, modelo y año")
    payload = data.model_dump()
    payload.pop("owner_id", None)
    nuevo = Vehiculo(**payload)
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)
    estatus = db.query(Estatus).filter(Estatus.nombre == "Activo").first()
    if not estatus:
        estatus = Estatus(nombre="Activo")
        db.add(estatus)
        db.commit()
        db.refresh(estatus)
    estatus_id = estatus.id if estatus else 1
    relacion = PersonaVehiculo(id_persona=usuario.id_persona, id_vehiculo=nuevo.id, id_estatus=estatus_id)
    db.add(relacion)
    db.commit()
    return nuevo

@car.put("/{vehiculo_id}")
def update(vehiculo_id: int, data: VehiculoCreate, db: Session = Depends(get_db)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="No encontrado")

    for key, value in data.model_dump().items():
        if key == "owner_id":
            continue
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

    relaciones = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_vehiculo == vehiculo.id).all()
    for r in relaciones:
        db.delete(r)
    db.commit()
    db.delete(vehiculo)
    db.commit()
    return {"msg": "Vehiculo eliminado"}
