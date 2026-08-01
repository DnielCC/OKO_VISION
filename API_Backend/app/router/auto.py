import logging

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Vehiculo, PersonaVehiculo, Usuario, Estatus, LaravelVehicle
from app.models.cars import VehiculoCreate, VehiculoUpdate
from app.security.auth import obtener_usuario_actual

vehiculos = APIRouter(prefix="/vehiculos", tags=["Vehículos"])
logger = logging.getLogger(__name__)


def _sync_laravel_vehicle(
    db: Session,
    *,
    vehiculo_id: int,
    owner_id: int,
    plate: str | None,
    marca: str,
    modelo: str,
    color: str | None,
):
    if not plate:
        return

    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo_id).first()
    if not mirror:
        mirror = LaravelVehicle(
            id=vehiculo_id,
            owner_id=owner_id,
            plate=plate,
            brand=marca,
            model=modelo,
            color=color or "Sin definir",
        )
        db.add(mirror)
    else:
        mirror.owner_id = owner_id
        mirror.plate = plate
        mirror.brand = marca
        mirror.model = modelo
        mirror.color = color or "Sin definir"


def _serialize_vehicle(v: Vehiculo, owner_id: int, mirror: LaravelVehicle | None) -> dict:
    return {
        "id": v.id,
        "plate": mirror.plate if mirror else f"VEH-{v.id:03d}",
        "marca": v.marca,
        "modelo": v.modelo,
        "anio": v.anio,
        "color": v.color,
        "tipo": v.tipo,
        "owner_id": owner_id,
    }

@vehiculos.get("/")
def obtener_todos(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    resultados = db.query(Vehiculo, PersonaVehiculo, Usuario).join(
        PersonaVehiculo, Vehiculo.id == PersonaVehiculo.id_vehiculo
    ).join(
        Usuario, PersonaVehiculo.id_persona == Usuario.id_persona
    ).distinct().all()
    
    salida = []
    for v, pv, u in resultados:
        mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == v.id).first()
        salida.append(_serialize_vehicle(v, u.id, mirror))
    return salida

@vehiculos.get("/{vehiculo_id}")
def obtener_uno(vehiculo_id: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="Vehículo no encontrado")
    relacion = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_vehiculo == vehiculo.id).first()
    persona_id = relacion.id_persona if relacion else None
    owner = db.query(Usuario).filter(Usuario.id_persona == persona_id).first() if persona_id else None
    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo.id).first()
    return _serialize_vehicle(vehiculo, owner.id if owner else usuario_actual.id, mirror)

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
    carga_util.pop("plate", None)
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
    try:
        _sync_laravel_vehicle(
            db,
            vehiculo_id=nuevo.id,
            owner_id=usuario.id,
            plate=datos.plate,
            marca=nuevo.marca,
            modelo=nuevo.modelo,
            color=nuevo.color,
        )
        db.commit()
    except Exception as exc:
        db.rollback()
        logger.warning("No se pudo sincronizar vehicles para vehiculo %s: %s", nuevo.id, exc)
    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == nuevo.id).first()
    return _serialize_vehicle(nuevo, usuario.id, mirror)

@vehiculos.put("/{vehiculo_id}")
def actualizar(vehiculo_id: int, datos: VehiculoCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="No encontrado")

    for clave, valor in datos.model_dump().items():
        if clave in {"owner_id", "plate"}:
            continue
        setattr(vehiculo, clave, valor)
    db.commit()

    try:
        _sync_laravel_vehicle(
            db,
            vehiculo_id=vehiculo.id,
            owner_id=datos.owner_id,
            plate=datos.plate,
            marca=vehiculo.marca,
            modelo=vehiculo.modelo,
            color=vehiculo.color,
        )
        db.commit()
    except Exception as exc:
        db.rollback()
        logger.warning("No se pudo sincronizar vehicles para vehiculo %s: %s", vehiculo.id, exc)
        db.commit()
    owner_id = datos.owner_id or usuario_actual.id
    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo.id).first()
    return _serialize_vehicle(vehiculo, owner_id, mirror)

@vehiculos.patch("/{vehiculo_id}")
def parchear(vehiculo_id: int, datos: VehiculoUpdate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="No encontrado")

    for clave, valor in datos.model_dump(exclude_unset=True).items():
        if clave == "plate":
            continue
        setattr(vehiculo, clave, valor)
    db.commit()

    relacion = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_vehiculo == vehiculo.id).first()
    owner = db.query(Usuario).filter(Usuario.id_persona == relacion.id_persona).first() if relacion else None
    try:
        _sync_laravel_vehicle(
            db,
            vehiculo_id=vehiculo.id,
            owner_id=owner.id if owner else usuario_actual.id,
            plate=datos.plate if "plate" in datos.model_dump(exclude_unset=True) else None,
            marca=vehiculo.marca,
            modelo=vehiculo.modelo,
            color=vehiculo.color,
        )
        db.commit()
    except Exception as exc:
        db.rollback()
        logger.warning("No se pudo sincronizar vehicles para vehiculo %s: %s", vehiculo.id, exc)
        db.commit()
    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo.id).first()
    return _serialize_vehicle(vehiculo, owner.id if owner else usuario_actual.id, mirror)

@vehiculos.delete("/{vehiculo_id}")
def eliminar(vehiculo_id: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        raise HTTPException(status_code=404, detail="No encontrado")

    relaciones = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_vehiculo == vehiculo.id).all()
    for r in relaciones:
        db.delete(r)
    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo.id).first()
    if mirror:
        db.delete(mirror)
    db.commit()
    db.delete(vehiculo)
    db.commit()
    return {"msg": "Vehículo eliminado"}
