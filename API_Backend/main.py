from fastapi import FastAPI, Depends, HTTPException, status
from sqlalchemy.orm import Session
import models
from database import engine, Base, get_db
from pydantic import BaseModel
from typing import List, Optional
import datetime

import time

# Reintentar conexión a la base de datos (por si Postgres no está listo)
for i in range(10):
    try:
        # Crear tablas
        models.Base.metadata.create_all(bind=engine)
        # Seeding inicial
        from seed import seed_db, get_password_hash
        seed_db()
        print("Database connection and seeding successful!")
        break
    except Exception as e:
        print(f"Waiting for database... ({i+1}/10) Error: {e}")
        time.sleep(3)

app = FastAPI(title="OKO VISION API", description="Backend para el Sistema de Control de Acceso Inteligente")

# Esquemas Pydantic
class UserBase(BaseModel):
    username: str
    email: str

class UserCreate(UserBase):
    password: str

class UserResponse(UserBase):
    id: int
    is_admin: bool
    created_at: datetime.datetime
    class Config:
        from_attributes = True

class VehicleBase(BaseModel):
    plate: str
    brand: str
    model: str
    color: str

class VehicleResponse(VehicleBase):
    id: int
    owner_id: int
    class Config:
        from_attributes = True

class AccessLogResponse(BaseModel):
    id: int
    user_id: int
    vehicle_plate: str
    access_time: datetime.datetime
    access_type: str
    is_authorized: bool
    class Config:
        from_attributes = True

# Endpoints
@app.get("/")
def read_root():
    return {"status": "online", "message": "OKO VISION API is running"}

# --- Usuarios ---
@app.post("/users/", response_model=UserResponse, status_code=status.HTTP_201_CREATED)
def create_user(user: UserCreate, db: Session = Depends(get_db)):
    db_user = db.query(models.User).filter(models.User.username == user.username).first()
    if db_user:
        raise HTTPException(status_code=400, detail="Username already registered")
    
    new_user = models.User(
        username=user.username,
        email=user.email,
        hashed_password=get_password_hash(user.password)
    )
    db.add(new_user)
    db.commit()
    db.refresh(new_user)
    return new_user

@app.get("/users/", response_model=List[UserResponse])
def get_users(db: Session = Depends(get_db)):
    return db.query(models.User).all()

# --- Vehículos ---
@app.get("/vehicles/", response_model=List[VehicleResponse])
def get_vehicles(db: Session = Depends(get_db)):
    return db.query(models.Vehicle).all()

# --- Accesos ---
@app.get("/access-logs/", response_model=List[AccessLogResponse])
def get_access_logs(db: Session = Depends(get_db)):
    return db.query(models.AccessLog).all()

# --- Alertas ---
@app.get("/alerts/")
def get_alerts(db: Session = Depends(get_db)):
    return db.query(models.Alert).all()
