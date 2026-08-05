from datetime import datetime
from typing import Optional

from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel
from sqlalchemy.orm import Session

from app.data.db import get_db
from app.data.database import LaravelAlert, Usuario
from app.security.auth import obtener_usuario_actual


router = APIRouter(prefix="/alerts", tags=["Alertas"])


class AlertUpdate(BaseModel):
    notes: Optional[str] = None
    status: Optional[str] = None
    is_resolved: Optional[bool] = None


def _serialize_alert(alert: LaravelAlert) -> dict:
    status = alert.status or ("RESOLVED" if alert.is_resolved else "PENDING")
    return {
        "id": alert.id,
        "title": alert.title,
        "description": alert.description,
        "severity": str(alert.severity).upper(),
        "status": status,
        "vehicle_plate": alert.vehicle_plate,
        "vehicle_owner": alert.vehicle_owner,
        "location": alert.location,
        "created_at": alert.created_at.isoformat() if alert.created_at else None,
        "resolved_at": alert.resolved_at.isoformat() if alert.resolved_at else None,
        "image_url": alert.image_url,
        "image_uri": alert.image_url,
        "notes": alert.notes,
        "is_resolved": bool(alert.is_resolved),
        "camera": alert.camera,
    }


@router.get("")
def obtener_alertas(
    db: Session = Depends(get_db),
    usuario_actual: Usuario = Depends(obtener_usuario_actual),
):
    alertas = (
        db.query(LaravelAlert)
        .order_by(LaravelAlert.created_at.desc(), LaravelAlert.id.desc())
        .all()
    )
    return [_serialize_alert(alerta) for alerta in alertas]


@router.get("/{alerta_id}")
def obtener_alerta(
    alerta_id: int,
    db: Session = Depends(get_db),
    usuario_actual: Usuario = Depends(obtener_usuario_actual),
):
    alerta = db.query(LaravelAlert).filter(LaravelAlert.id == alerta_id).first()
    if not alerta:
        raise HTTPException(status_code=404, detail="Alerta no encontrada")
    return _serialize_alert(alerta)


@router.patch("/{alerta_id}")
def actualizar_alerta(
    alerta_id: int,
    datos: AlertUpdate,
    db: Session = Depends(get_db),
    usuario_actual: Usuario = Depends(obtener_usuario_actual),
):
    alerta = db.query(LaravelAlert).filter(LaravelAlert.id == alerta_id).first()
    if not alerta:
        raise HTTPException(status_code=404, detail="Alerta no encontrada")

    payload = datos.model_dump(exclude_unset=True)

    if "notes" in payload:
        alerta.notes = payload["notes"]

    if "status" in payload and payload["status"]:
        alerta.status = str(payload["status"]).upper()

    if "is_resolved" in payload:
        alerta.is_resolved = bool(payload["is_resolved"])

    if alerta.status == "RESOLVED" or alerta.is_resolved:
        alerta.status = "RESOLVED"
        alerta.is_resolved = True
        alerta.resolved_at = alerta.resolved_at or datetime.utcnow()
    elif alerta.status:
        alerta.is_resolved = alerta.status == "RESOLVED"
        if not alerta.is_resolved:
            alerta.resolved_at = None

    db.commit()
    db.refresh(alerta)
    return _serialize_alert(alerta)
