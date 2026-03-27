from sqlalchemy import Column, Integer, String, Date, DateTime, ForeignKey, Enum
from sqlalchemy.dialects.postgresql import ENUM as PG_ENUM
from app.data.db import Base
import datetime

# --- TABLAS INDEPENDIENTES ---

class Carrera(Base):
    __tablename__ = "carreras"
    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(250), nullable=False)

class Departamento(Base):
    __tablename__ = "departamentos"
    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(250), nullable=False)

class Estatus(Base):
    __tablename__ = "estatus"
    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(100), nullable=False)

class Persona(Base):
    __tablename__ = "personas"
    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(200), nullable=False)
    apellidos = Column(String(300), nullable=False)
    fecha_nacimiento = Column(Date)
    sexo = Column(PG_ENUM('H', 'M', name='sexo_enum'))
    foto = Column(String(500))
    telefono = Column(String(10))
    mail = Column(String(50))

class Rol(Base):
    __tablename__ = "roles"
    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(200), nullable=False)

class Vehiculo(Base):
    __tablename__ = "vehiculo"
    id = Column(Integer, primary_key=True, index=True)
    marca = Column(String(100), nullable=False)
    modelo = Column(String(100), nullable=False)
    anio = Column(Integer)
    color = Column(String(50))
    tipo = Column(PG_ENUM('auto', 'moto', 'camioneta', 'otro', name='tipo_vehiculo_enum'), nullable=False)

class Dispositivo(Base):
    __tablename__ = "dispositivos"
    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(250), nullable=False)

class Puerta(Base):
    __tablename__ = "puertas"
    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(250), nullable=False)

# --- TABLAS CON RELACIONES ---

class Usuario(Base):
    __tablename__ = "usuarios"
    id = Column(Integer, primary_key=True, index=True)
    id_persona = Column(Integer, ForeignKey("personas.id"), nullable=False)
    id_carrera = Column(Integer, ForeignKey("carreras.id"))
    id_departamento = Column(Integer, ForeignKey("departamentos.id"))
    id_rol = Column(Integer, ForeignKey("roles.id"), nullable=False)
    identificador = Column(String(15), nullable=False)

class Visitante(Base):
    __tablename__ = "visitantes"
    id = Column(Integer, primary_key=True, index=True)
    id_persona = Column(Integer, ForeignKey("personas.id"), nullable=False)
    identificacion = Column(String(500))
    motivo_visita = Column(String(600))
    id_departamento = Column(Integer, ForeignKey("departamentos.id"), nullable=False)
    iden_temp = Column(String(15))

class PersonaVehiculo(Base):
    __tablename__ = "personas_vehiculos"
    id = Column(Integer, primary_key=True, index=True)
    id_persona = Column(Integer, ForeignKey("personas.id"), nullable=False)
    id_vehiculo = Column(Integer, ForeignKey("vehiculo.id"), nullable=False)
    id_estatus = Column(Integer, ForeignKey("estatus.id"), nullable=False)

# --- TRANSACCIONES ---

class Permiso(Base):
    __tablename__ = "permisos"
    id = Column(Integer, primary_key=True, index=True)
    id_usuario = Column(Integer, ForeignKey("usuarios.id"), nullable=False)
    estatus = Column(Integer, nullable=False)
    emision = Column(Date, nullable=False)
    vencimiento = Column(Date, nullable=False)

class Acceso(Base):
    __tablename__ = "accesos"
    id = Column(Integer, primary_key=True, index=True)
    id_persona = Column(Integer, ForeignKey("personas.id"), nullable=False)
    id_vehiculo = Column(Integer, ForeignKey("vehiculo.id"))
    id_puerta = Column(Integer, ForeignKey("puertas.id"), nullable=False)
    id_dispositivo = Column(Integer, ForeignKey("dispositivos.id"), nullable=False)
    fecha_hora = Column(DateTime, default=datetime.datetime.utcnow)
    tipo_acceso = Column(PG_ENUM('P', 'V', name='tipo_acceso_enum'), nullable=False)
    resultado = Column(PG_ENUM('p', 'd', name='resultado_enum'), nullable=False)
    metodo = Column(PG_ENUM('credencial', 'QR', 'Gafete', name='metodo_enum'), nullable=False)
    autoriza = Column(Integer)