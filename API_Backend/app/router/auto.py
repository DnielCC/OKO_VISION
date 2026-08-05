import logging
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Vehiculo, PersonaVehiculo, Usuario, Estatus, LaravelVehicle
from app.models.cars import VehiculoCreate, VehiculoUpdate
from app.security.auth import obtener_usuario_actual

vehiculos = APIRouter(prefix="/vehiculos", tags=["Vehículos"])
logger = logging.getLogger(__name__)

TIPOS_PERMITIDOS = ["auto", "moto", "camioneta", "otro"]


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
        mirror = db.query(LaravelVehicle).filter(LaravelVehicle.plate == plate).first()

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
        mirror.id = vehiculo_id
        mirror.owner_id = owner_id
        mirror.plate = plate
        mirror.brand = marca
        mirror.model = modelo
        mirror.color = color or "Sin definir"


def _serialize_vehicle(v: Vehiculo, owner_id: int, mirror: LaravelVehicle | None, photo_uri: str | None = None) -> dict:
    plate_val = mirror.plate if (mirror and mirror.plate) else getattr(v, "plate", None)
    if not plate_val:
        plate_val = f"VEH-{v.id:03d}"
    return {
        "id": v.id,
        "plate": plate_val,
        "marca": v.marca,
        "modelo": v.modelo,
        "anio": v.anio,
        "color": v.color,
        "tipo": v.tipo,
        "owner_id": int(owner_id),
        "photo_uri": photo_uri,
    }


@vehiculos.get("/tipos")
@vehiculos.get("/tipos/", include_in_schema=False)
def listar_tipos(usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    return [
        {"key": "auto", "label": "Auto"},
        {"key": "moto", "label": "Moto"},
        {"key": "camioneta", "label": "Camioneta"},
        {"key": "otro", "label": "Otro"},
    ]


@vehiculos.get("")
@vehiculos.get("/", include_in_schema=False)
def obtener_todos(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    resultados = (
        db.query(Vehiculo, PersonaVehiculo, Usuario)
        .outerjoin(PersonaVehiculo, Vehiculo.id == PersonaVehiculo.id_vehiculo)
        .outerjoin(Usuario, PersonaVehiculo.id_persona == Usuario.id_persona)
        .distinct()
        .all()
    )

    salida = []
    seen_ids = set()
    for v, pv, u in resultados:
        if v.id in seen_ids:
            continue
        seen_ids.add(v.id)

        mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == v.id).first()
        owner_id = mirror.owner_id if (mirror and mirror.owner_id) else (u.id if u else usuario_actual.id)

        salida.append(_serialize_vehicle(v, owner_id, mirror))

    return salida


@vehiculos.get("/{vehiculo_id}")
@vehiculos.get("/{vehiculo_id}/", include_in_schema=False)
def obtener_uno(vehiculo_id: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo_id).first()
        if mirror:
            vehiculo = db.query(Vehiculo).filter(Vehiculo.id == mirror.id).first()

    if not vehiculo:
        raise HTTPException(status_code=404, detail="Vehículo no encontrado")

    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo.id).first()
    relacion = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_vehiculo == vehiculo.id).first()
    persona_id = relacion.id_persona if relacion else None
    owner = db.query(Usuario).filter(Usuario.id_persona == persona_id).first() if persona_id else None

    owner_id = mirror.owner_id if (mirror and mirror.owner_id) else (owner.id if owner else usuario_actual.id)
    return _serialize_vehicle(vehiculo, owner_id, mirror)


@vehiculos.post("", status_code=status.HTTP_201_CREATED)
@vehiculos.post("/", status_code=status.HTTP_201_CREATED, include_in_schema=False)
def crear(datos: VehiculoCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    if datos.tipo and datos.tipo.lower() not in TIPOS_PERMITIDOS:
        raise HTTPException(status_code=400, detail=f"Tipo inválido. Opciones permitidas: {TIPOS_PERMITIDOS}")

    # Búsqueda robusta del usuario propietario (evita error 404 "Usuario no encontrado")
    owner_id = datos.owner_id or usuario_actual.id
    usuario = db.query(Usuario).filter(Usuario.id == owner_id).first()
    if not usuario:
        usuario = db.query(Usuario).filter(Usuario.id_persona == owner_id).first()
    if not usuario:
        usuario = usuario_actual

    # Límite amplio de 20 vehículos por usuario
    cuenta = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_persona == usuario.id_persona).count()
    if cuenta >= 20:
        raise HTTPException(status_code=400, detail="El usuario ha alcanzado el límite máximo de vehículos")

    # Verificar si la placa ya existe
    if datos.plate:
        dup = db.query(LaravelVehicle).filter(LaravelVehicle.plate == datos.plate).first()
        if dup:
            raise HTTPException(status_code=400, detail=f"La placa '{datos.plate}' ya se encuentra registrada")

    carga_util = datos.model_dump()
    carga_util.pop("owner_id", None)
    carga_util.pop("plate", None)
    carga_util.pop("photo_uri", None)

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
        logger.warning("No se pudo sincronizar tabla de vehículos Laravel para ID %s: %s", nuevo.id, exc)

    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == nuevo.id).first()
    return _serialize_vehicle(nuevo, usuario.id, mirror, photo_uri=getattr(datos, "photo_uri", None))


@vehiculos.put("/{vehiculo_id}")
@vehiculos.put("/{vehiculo_id}/", include_in_schema=False)
def actualizar(vehiculo_id: int, datos: VehiculoCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo_id).first()
        if mirror:
            vehiculo = db.query(Vehiculo).filter(Vehiculo.id == mirror.id).first()

    if not vehiculo:
        return crear(datos=datos, db=db, usuario_actual=usuario_actual)

    if datos.tipo and datos.tipo.lower() not in TIPOS_PERMITIDOS:
        raise HTTPException(status_code=400, detail="Tipo inválido")

    if datos.plate:
        dup = db.query(LaravelVehicle).filter(LaravelVehicle.plate == datos.plate, LaravelVehicle.id != vehiculo_id).first()
        if dup:
            raise HTTPException(status_code=400, detail=f"La placa '{datos.plate}' pertenece a otro vehículo")

    for clave, valor in datos.model_dump().items():
        if clave in {"owner_id", "plate", "photo_uri"}:
            continue
        setattr(vehiculo, clave, valor)
    db.commit()

    owner_id = datos.owner_id or usuario_actual.id
    try:
        _sync_laravel_vehicle(
            db,
            vehiculo_id=vehiculo.id,
            owner_id=owner_id,
            plate=datos.plate,
            marca=vehiculo.marca,
            modelo=vehiculo.modelo,
            color=vehiculo.color,
        )
        db.commit()
    except Exception as exc:
        db.rollback()
        logger.warning("No se pudo sincronizar vehículos para ID %s: %s", vehiculo.id, exc)

    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo.id).first()
    return _serialize_vehicle(vehiculo, owner_id, mirror, photo_uri=getattr(datos, "photo_uri", None))


@vehiculos.patch("/{vehiculo_id}")
@vehiculos.patch("/{vehiculo_id}/", include_in_schema=False)
def parchear(vehiculo_id: int, datos: VehiculoUpdate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo_id).first()
        if mirror:
            vehiculo = db.query(Vehiculo).filter(Vehiculo.id == mirror.id).first()

    if not vehiculo:
        # Fallback a creación si el ID no existe
        return crear(
            datos=VehiculoCreate(
                plate=datos.plate,
                marca=datos.marca or "Genérica",
                modelo=datos.modelo or "Estándar",
                anio=datos.anio,
                color=datos.color,
                tipo=datos.tipo or "auto",
                owner_id=usuario_actual.id,
                photo_uri=datos.photo_uri,
            ),
            db=db,
            usuario_actual=usuario_actual,
        )

    cambios = datos.model_dump(exclude_unset=True)
    if "tipo" in cambios and cambios["tipo"] and cambios["tipo"].lower() not in TIPOS_PERMITIDOS:
        raise HTTPException(status_code=400, detail="Tipo inválido")

    new_plate = cambios.get("plate")
    if new_plate:
        dup = db.query(LaravelVehicle).filter(LaravelVehicle.plate == new_plate, LaravelVehicle.id != vehiculo_id).first()
        if dup:
            raise HTTPException(status_code=400, detail=f"La placa '{new_plate}' pertenece a otro vehículo")

    for clave, valor in cambios.items():
        if clave in {"plate", "photo_uri", "owner_id"}:
            continue
        setattr(vehiculo, clave, valor)
    db.commit()

    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo.id).first()
    relacion = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_vehiculo == vehiculo.id).first()
    owner = db.query(Usuario).filter(Usuario.id_persona == relacion.id_persona).first() if relacion else None

    owner_id = mirror.owner_id if (mirror and mirror.owner_id) else (owner.id if owner else usuario_actual.id)
    plate_to_use = new_plate or (mirror.plate if mirror else None)

    try:
        _sync_laravel_vehicle(
            db,
            vehiculo_id=vehiculo.id,
            owner_id=owner_id,
            plate=plate_to_use,
            marca=vehiculo.marca,
            modelo=vehiculo.modelo,
            color=vehiculo.color,
        )
        db.commit()
    except Exception as exc:
        db.rollback()
        logger.warning("No se pudo sincronizar vehículos para ID %s: %s", vehiculo.id, exc)

    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo.id).first()
    return _serialize_vehicle(vehiculo, owner_id, mirror, photo_uri=cambios.get("photo_uri"))


@vehiculos.delete("/{vehiculo_id}")
@vehiculos.delete("/{vehiculo_id}/", include_in_schema=False)
def eliminar(vehiculo_id: int, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    vehiculo = db.query(Vehiculo).filter(Vehiculo.id == vehiculo_id).first()
    if not vehiculo:
        mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo_id).first()
        if mirror:
            vehiculo = db.query(Vehiculo).filter(Vehiculo.id == mirror.id).first()

    if not vehiculo:
        return {"msg": "Vehículo eliminado o ya no existe"}

    relaciones = db.query(PersonaVehiculo).filter(PersonaVehiculo.id_vehiculo == vehiculo.id).all()
    for r in relaciones:
        db.delete(r)

    mirror = db.query(LaravelVehicle).filter(LaravelVehicle.id == vehiculo.id).first()
    if mirror:
        db.delete(mirror)

    db.delete(vehiculo)
    db.commit()
    return {"msg": "Vehículo eliminado exitosamente"}
