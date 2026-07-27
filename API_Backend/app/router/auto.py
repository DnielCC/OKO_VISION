from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Vehiculo, PersonaVehiculo, Usuario, Estatus
from app.models.cars import VehiculoCreate, VehiculoUpdate
from app.security.auth import obtener_usuario_actual

vehiculos = APIRouter(prefix="/vehiculos", tags=["Vehículos"])

@vehiculos.get("/")
def obtener_todos(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    resultados = db.query(Vehiculo, PersonaVehiculo, Usuario).join(
        PersonaVehiculo, Vehiculo.id == PersonaVehiculo.id_vehiculo
    ).join(
        Usuario, PersonaVehiculo.id_persona == Usuario.id_persona
    ).distinct().all()
    
    salida = []
    for v, pv, u in resultados:
        salida.append({
            "id": v.id,
            "marca": v.marca,
            "modelo": v.modelo,
            "anio": v.anio,
            "color": v.color,
            "tipo": v.tipo,
            "owner_id": u.id
        })
    return salida

@vehiculos.get("/{vehiculo_id}")
def obtener_uno(vehiculo_id: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="Vehículo no encontrado")
    return vehiculo

@vehiculos.post("/", status_code=status.HTTP_201_CREATED)
def crear(datos: VehiculoCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    usuario = db.query(Usuario).filter(Usuario.id == datos.owner_id).first()
    if not usuario:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    cuenta = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_persona == usuario.id_persona).count()
    if cuenta >= 2:
        raise HTTPException(status_code=400, detail="El usuario ya tiene el máximo de 2 vehículos")
    duplicado = db.query(Vehiculo, PersonaVehiculo).join(PersonaVehiculo, Vehiculo.id == PersonaVehiculo.id_vehiculo)\
        .filter(PersonaVehiculo.id_persona == usuario.id_persona, Vehiculo.marca == datos.marca, Vehiculo.modelo == datos.modelo, Vehiculo.anio == datos.anio).first()
    if duplicado:
        raise HTTPException(status_code=400, detail="Ya tienes un vehículo con la misma marca, modelo y año")
    carga_util = datos.model_dump()
    carga_util.pop("owner_id", None)
    nuevo = Vehiculo(**carga_util)
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

@vehiculos.put("/{vehiculo_id}")
def actualizar(vehiculo_id: int, datos: VehiculoCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="No encontrado")

    for clave, valor in datos.model_dump().items():
        if clave == "owner_id":
            continue
        setattr(vehiculo, clave, valor)

    db.commit()
    return vehiculo

@vehiculos.patch("/{vehiculo_id}")
def parchear(vehiculo_id: int, datos: VehiculoUpdate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="No encontrado")

    for clave, valor in datos.model_dump(exclude_unset=True).items():
        setattr(vehiculo, clave, valor)

    db.commit()
    return vehiculo

@vehiculos.delete("/{vehiculo_id}")
def eliminar(vehiculo_id: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="No encontrado")

    relaciones = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_vehiculo == vehiculo.id).all()
    for r in relaciones:
        db.delete(r)
    db.commit()
    db.delete(vehiculo)
    db.commit()
    return {"msg": "Vehículo eliminado"}
