from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, declarative_base
import os

#URL de conexión 
DATABASE_URL = os.getenv(
    "DATABASE_URL",
    "postgresql://admin:123456@localhost:5432/DB_OKO" #CONFIGURAR EN EL PROYECTO
)

# Motor de conexión con pool ajustado para soportar mayor concurrencia
engine = create_engine(
    DATABASE_URL,
    pool_pre_ping=True,
    pool_size=int(os.getenv("DB_POOL_SIZE", "20")),
    max_overflow=int(os.getenv("DB_MAX_OVERFLOW", "40")),
    pool_timeout=int(os.getenv("DB_POOL_TIMEOUT", "30")),
    pool_recycle=int(os.getenv("DB_POOL_RECYCLE", "1800")),
)

#Gestionador de sesiones
SessionLocal = sessionmaker(
    autocommit = False,
    autoflush = False,
    bind = engine
)

#Base declarativa
Base = declarative_base()

#Sesión de cada petición
def get_db():
    db = SessionLocal()
    try:
        yield db 
    finally:
        db.close()
