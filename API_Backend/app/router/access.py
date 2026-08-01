import logging
from datetime import datetime
from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from app.data.db import get_db
from app.data.database import Acceso, Persona, Puerta, Dispositivo, Usuario, LaravelAccessLog, LaravelVehicle
from pydantic import BaseModel
from typing import Optional
from app.security.auth import obtener_usuario_actual

class AccesoCreate(BaseModel):
    id_persona: Optional[int] = None
    id_vehiculo: Optional[int] = None
    id_puerta: Optional[int] = None
    id_dispositivo: Optional[int] = None
    tipo_acceso: Optional[str] = None
    resultado: Optional[str] = None
    metodo: Optional[str] = None
    autoriza: Optional[int] = None
    user_id: Optional[int] = None
    type: Optional[str] = None
    method: Optional[str] = None
    timestamp: Optional[datetime] = None
    notes: Optional[str] = None
    vehicle_plate: Optional[str] = None

router = APIRouter(prefix="/accesos", tags=["Accesos"])
logger = logging.getLogger(__name__)


def _to_mobile_type(tipo_acceso: str) -> str:
    return "entry" if str(tipo_acceso).upper() == "P" else "exit"


def _to_api_type(tipo_acceso: str) -> str:
    return "ENTRY" if _to_mobile_type(tipo_acceso) == "entry" else "EXIT"


def _to_mobile_method(metodo: Optional[str]) -> Optional[str]:
    if not metodo:
        return None
    raw = str(metodo).strip().lower()
    if raw == "qr":
        return "qr"
    if raw == "credencial":
        return "manual"
    if raw == "gafete":
        return "plate"
    return raw


def _resolve_persona_id(datos: AccesoCreate, db: Session) -> int:
    if datos.id_persona:
        return datos.id_persona
    if datos.user_id:
        usuario = db.query(Usuario).filter(Usuario.id == datos.user_id).first()
        if usuario:
            return usuario.id_persona
        raise HTTPException(status_code=404, detail="Usuario no encontrado para registrar acceso")
    raise HTTPException(status_code=422, detail="id_persona o user_id es requerido")


def _resolve_puerta_id(datos: AccesoCreate, db: Session) -> int:
    if datos.id_puerta:
        return datos.id_puerta
    puerta = db.query(Puerta).order_by(Puerta.id.asc()).first()
    if puerta:
        return puerta.id
    raise HTTPException(status_code=400, detail="No hay puertas configuradas")


def _resolve_dispositivo_id(datos: AccesoCreate, db: Session) -> int:
    if datos.id_dispositivo:
        return datos.id_dispositivo
    dispositivo = db.query(Dispositivo).order_by(Dispositivo.id.asc()).first()
    if dispositivo:
        return dispositivo.id
    raise HTTPException(status_code=400, detail="No hay dispositivos configurados")


def _normalize_tipo_acceso(datos: AccesoCreate) -> str:
    if datos.tipo_acceso:
        value = str(datos.tipo_acceso).strip().upper()
        if value in {"P", "V"}:
            return value
    if datos.type:
        return "P" if str(datos.type).strip().lower() == "entry" else "V"
    return "P"


def _normalize_resultado(datos: AccesoCreate) -> str:
    if datos.resultado:
        value = str(datos.resultado).strip().lower()
        if value in {"p", "d"}:
            return value
    return "p"


def _normalize_metodo(datos: AccesoCreate) -> str:
    origen = datos.metodo or datos.method or "QR"
    value = str(origen).strip().lower()
    if value == "qr":
        return "QR"
    if value in {"manual", "credencial"}:
        return "credencial"
    if value in {"plate", "gafete"}:
        return "Gafete"
    return "credencial"


def _serialize_access(acceso: Acceso, persona: Optional[Persona], usuario: Optional[Usuario]) -> dict:
    mobile_type = _to_mobile_type(acceso.tipo_acceso)
    mobile_method = _to_mobile_method(acceso.metodo)
    authorized = acceso.resultado == "p"
    full_name = None
    if persona:
        full_name = f"{persona.nombre} {persona.apellidos}".strip()

    return {
        "id": acceso.id,
        "user_id": usuario.id if usuario else acceso.id_persona,
        "user_name": full_name,
        "vehicle_plate": "N/A",
        "timestamp": acceso.fecha_hora.isoformat() if acceso.fecha_hora else None,
        "type": mobile_type,
        "method": mobile_method,
        "notes": "Acceso autorizado" if authorized else "Acceso denegado",
        "access_time": acceso.fecha_hora.isoformat() if acceso.fecha_hora else None,
        "access_type": _to_api_type(acceso.tipo_acceso),
        "is_authorized": authorized,
    }


def _resolve_vehicle_plate(datos: AccesoCreate, user_id: int | None, db: Session) -> str:
    if datos.vehicle_plate:
        return str(datos.vehicle_plate).strip().upper()
    if user_id:
        vehicle = (
            db.query(LaravelVehicle)
            .filter(LaravelVehicle.owner_id == user_id)
            .order_by(LaravelVehicle.id.desc())
            .first()
        )
        if vehicle and vehicle.plate:
            return vehicle.plate
    return "N/A"


def _sync_laravel_access_log(
    db: Session,
    *,
    acceso_id: int,
    user_id: int,
    vehicle_plate: str,
    access_time: datetime,
    access_type: str,
    is_authorized: bool,
):
    mirror = db.query(LaravelAccessLog).filter(LaravelAccessLog.id == acceso_id).first()
    if not mirror:
        mirror = LaravelAccessLog(
            id=acceso_id,
            user_id=user_id,
            vehicle_plate=vehicle_plate,
            access_time=access_time,
            access_type=access_type,
            is_authorized=is_authorized,
        )
        db.add(mirror)
    else:
        mirror.user_id = user_id
        mirror.vehicle_plate = vehicle_plate
        mirror.access_time = access_time
        mirror.access_type = access_type
        mirror.is_authorized = is_authorized


@router.get("/")
def obtener_todos(db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    resultados = (
        db.query(Acceso, Persona, Usuario, LaravelAccessLog)
        .join(Persona, Acceso.id_persona == Persona.id)
        .outerjoin(Usuario, Usuario.id_persona == Acceso.id_persona)
        .outerjoin(LaravelAccessLog, LaravelAccessLog.id == Acceso.id)
        .order_by(Acceso.fecha_hora.desc())
        .all()
    )
    salida = []
    for acceso, persona, usuario, mirror in resultados:
        item = _serialize_access(acceso, persona, usuario)
        if mirror:
            item["vehicle_plate"] = mirror.vehicle_plate
            item["access_time"] = mirror.access_time.isoformat() if mirror.access_time else item["access_time"]
            item["access_type"] = mirror.access_type or item["access_type"]
            item["is_authorized"] = bool(mirror.is_authorized)
        salida.append(item)
    return salida


@router.post("/")
def crear(datos: AccesoCreate, db: Session = Depends(get_db), usuario_actual: Usuario = Depends(obtener_usuario_actual)):
    user_id = datos.user_id or usuario_actual.id
    payload = {
        "id_persona": _resolve_persona_id(datos, db),
        "id_vehiculo": datos.id_vehiculo,
        "id_puerta": _resolve_puerta_id(datos, db),
        "id_dispositivo": _resolve_dispositivo_id(datos, db),
        "fecha_hora": datos.timestamp or datetime.utcnow(),
        "tipo_acceso": _normalize_tipo_acceso(datos),
        "resultado": _normalize_resultado(datos),
        "metodo": _normalize_metodo(datos),
        "autoriza": datos.autoriza,
    }
    nuevo = Acceso(**payload)
    db.add(nuevo)
    db.commit()
    db.refresh(nuevo)

    persona = db.query(Persona).filter(Persona.id == nuevo.id_persona).first()
    usuario = db.query(Usuario).filter(Usuario.id_persona == nuevo.id_persona).first()
    try:
        _sync_laravel_access_log(
            db,
            acceso_id=nuevo.id,
            user_id=user_id,
            vehicle_plate=_resolve_vehicle_plate(datos, user_id, db),
            access_time=nuevo.fecha_hora,
            access_type=_to_api_type(nuevo.tipo_acceso),
            is_authorized=nuevo.resultado == "p",
        )
        db.commit()
    except Exception as exc:
        db.rollback()
        logger.warning("No se pudo sincronizar access_logs para acceso %s: %s", nuevo.id, exc)
    item = _serialize_access(nuevo, persona, usuario)
    mirror = db.query(LaravelAccessLog).filter(LaravelAccessLog.id == nuevo.id).first()
    if mirror:
        item["vehicle_plate"] = mirror.vehicle_plate
    return item
