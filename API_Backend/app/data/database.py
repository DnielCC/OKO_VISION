from sqlalchemy import Column, Integer, String, Date, DateTime, ForeignKey, Enum, Boolean, Text, BigInteger
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
    foto = Column(Text)
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
    password = Column(String(255), nullable=True)  # Contraseña hasheada (nullable para retrocompatibilidad inicial)

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

# --- TABLAS COMPATIBILIDAD LARAVEL (MIGRACIONES FRONTEND) ---

class LaravelUser(Base):
    __tablename__ = 'users'
    id = Column(BigInteger, primary_key=True, index=True)
    name = Column(String(255), nullable=False)
    email = Column(String(255), unique=True, nullable=False)
    email_verified_at = Column(DateTime, nullable=True)
    password = Column(String(255), nullable=False)
    remember_token = Column(String(100), nullable=True)
    role = Column(PG_ENUM('admin', 'usuario', 'visitante', name='users_role_enum', create_type=False), default='usuario')
    telefono = Column(String(255), nullable=True)
    direccion = Column(String(255), nullable=True)
    activo = Column(Boolean, default=True)
    created_at = Column(DateTime, nullable=True)
    updated_at = Column(DateTime, nullable=True)

class PasswordResetToken(Base):
    __tablename__ = 'password_reset_tokens'
    email = Column(String(255), primary_key=True)
    token = Column(String(255), nullable=False)
    created_at = Column(DateTime, nullable=True)

class Session(Base):
    __tablename__ = 'sessions'
    id = Column(String(255), primary_key=True)
    user_id = Column(BigInteger, index=True, nullable=True)
    ip_address = Column(String(45), nullable=True)
    user_agent = Column(Text, nullable=True)
    payload = Column(Text, nullable=False)
    last_activity = Column(Integer, index=True, nullable=False)

class Cache(Base):
    __tablename__ = 'cache'
    key = Column(String(255), primary_key=True)
    value = Column(Text, nullable=False)
    expiration = Column(Integer, index=True, nullable=False)

class CacheLock(Base):
    __tablename__ = 'cache_locks'
    key = Column(String(255), primary_key=True)
    owner = Column(String(255), nullable=False)
    expiration = Column(Integer, index=True, nullable=False)

class Job(Base):
    __tablename__ = 'jobs'
    id = Column(BigInteger, primary_key=True, index=True)
    queue = Column(String(255), index=True, nullable=False)
    payload = Column(Text, nullable=False)
    attempts = Column(Integer, nullable=False)
    reserved_at = Column(Integer, nullable=True)
    available_at = Column(Integer, nullable=False)
    created_at = Column(Integer, nullable=False)

class JobBatch(Base):
    __tablename__ = 'job_batches'
    id = Column(String(255), primary_key=True)
    name = Column(String(255), nullable=False)
    total_jobs = Column(Integer, nullable=False)
    pending_jobs = Column(Integer, nullable=False)
    failed_jobs = Column(Integer, nullable=False)
    failed_job_ids = Column(Text, nullable=False)
    options = Column(Text, nullable=True)
    cancelled_at = Column(Integer, nullable=True)
    created_at = Column(Integer, nullable=False)
    finished_at = Column(Integer, nullable=True)

class FailedJob(Base):
    __tablename__ = 'failed_jobs'
    id = Column(BigInteger, primary_key=True, index=True)
    uuid = Column(String(255), unique=True, nullable=False)
    connection = Column(Text, nullable=False)
    queue = Column(Text, nullable=False)
    payload = Column(Text, nullable=False)
    exception = Column(Text, nullable=False)
    failed_at = Column(DateTime, default=datetime.datetime.utcnow, nullable=False)

class LaravelVehicle(Base):
    __tablename__ = 'vehicles'
    id = Column(BigInteger, primary_key=True, index=True)
    plate = Column(String(255), unique=True, nullable=False)
    brand = Column(String(255), nullable=False)
    model = Column(String(255), nullable=False)
    color = Column(String(255), nullable=False)
    owner_id = Column(BigInteger, ForeignKey('users.id', ondelete='CASCADE'), nullable=False)

class LaravelAccessLog(Base):
    __tablename__ = 'access_logs'
    id = Column(BigInteger, primary_key=True, index=True)
    user_id = Column(BigInteger, ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    vehicle_plate = Column(String(255), nullable=False)
    access_time = Column(DateTime, nullable=False)
    access_type = Column(String(255), nullable=False)
    is_authorized = Column(Boolean, nullable=False)

class LaravelAlert(Base):
    __tablename__ = 'alerts'
    id = Column(BigInteger, primary_key=True, index=True)
    title = Column(String(255), nullable=False)
    description = Column(Text, nullable=False)
    severity = Column(String(255), nullable=False)
    created_at = Column(DateTime, default=datetime.datetime.utcnow, nullable=False)
    is_resolved = Column(Boolean, default=False, nullable=False)