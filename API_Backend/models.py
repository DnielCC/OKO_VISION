from sqlalchemy import Column, Integer, String, DateTime, ForeignKey, Boolean
from sqlalchemy.orm import relationship
from database import Base
import datetime

class User(Base):
    __tablename__ = "users"

    id = Column(Integer, primary_key=True, index=True)
    username = Column(String, unique=True, index=True)
    email = Column(String, unique=True, index=True)
    hashed_password = Column(String)
    is_admin = Column(Boolean, default=False)
    created_at = Column(DateTime, default=datetime.datetime.utcnow)

    vehicles = relationship("Vehicle", back_populates="owner")
    access_logs = relationship("AccessLog", back_populates="user")

class Vehicle(Base):
    __tablename__ = "vehicles"

    id = Column(Integer, primary_key=True, index=True)
    plate = Column(String, unique=True, index=True)
    brand = Column(String)
    model = Column(String)
    color = Column(String)
    owner_id = Column(Integer, ForeignKey("users.id"))

    owner = relationship("User", back_populates="vehicles")

class AccessLog(Base):
    __tablename__ = "access_logs"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"))
    vehicle_plate = Column(String)
    access_time = Column(DateTime, default=datetime.datetime.utcnow)
    access_type = Column(String) # 'ENTRY' or 'EXIT'
    is_authorized = Column(Boolean, default=True)

    user = relationship("User", back_populates="access_logs")

class Alert(Base):
    __tablename__ = "alerts"

    id = Column(Integer, primary_key=True, index=True)
    title = Column(String)
    description = Column(String)
    severity = Column(String) # 'CRITICAL', 'HIGH', 'MEDIUM', 'LOW'
    created_at = Column(DateTime, default=datetime.datetime.utcnow)
    is_resolved = Column(Boolean, default=False)
