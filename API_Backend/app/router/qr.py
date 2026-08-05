import hashlib
import hmac
import json
import os
import time
import base64
from typing import Optional

from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session

from app.data.db import get_db
from app.data.database import Usuario, Persona, LaravelVehicle
from app.security.auth import obtener_usuario_actual

router = APIRouter(prefix="/qr", tags=["QR"])

SECRET_KEY = os.environ.get("OKO_QR_SECRET", "oko-vision-2026-qr-secret-key-change-me")


def _firmar(payload_dict: dict) -> str:
    data = json.dumps(payload_dict, sort_keys=True, separators=(",", ":"))
    sig = hmac.new(
        SECRET_KEY.encode("utf-8"),
        data.encode("utf-8"),
        hashlib.sha256,
    ).digest()
    return base64.urlsafe_b64encode(sig).decode("ascii").rstrip("=")


@router.get("/payload")
def generar_payload_qr(
    db: Session = Depends(get_db),
    user: Usuario = Depends(obtener_usuario_actual),
):
    persona = db.query(Persona).filter(Persona.id == user.id_persona).first()
    plate: Optional[str] = None
    vehiculo = (
        db.query(LaravelVehicle)
        .filter(LaravelVehicle.owner_id == user.id)
        .order_by(LaravelVehicle.id.desc())
        .first()
    )
    if vehiculo:
        plate = vehiculo.plate
    nombre_completo = None
    if persona:
        nombre_completo = (
            f"{persona.nombre or ''} {persona.apellidos or ''}".strip()
            or None
        )
    ts = int(time.time())
    payload_base = {
        "t": "okovision_user",
        "uid": user.id,
        "uname": nombre_completo or user.identificador or f"u_{user.id}",
        "email": persona.mail if persona else None,
        "v": 1,
        "ts": ts,
    }
    if plate:
        payload_base["plate"] = plate
    payload_base["sig"] = _firmar(payload_base)
    return payload_base
