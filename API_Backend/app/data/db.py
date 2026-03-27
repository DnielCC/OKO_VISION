from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, declarative_base
import os

#URL de conexión 
DATABASE_URL = os.getenv(
    "DATABASE_URL",
    "postgresql://admin:123456@localhost:5432/DB_OKO" #CONFIGURAR EN EL PROYECTO
)

#Motor de conexión
engine = create_engine(DATABASE_URL)

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