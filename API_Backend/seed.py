from sqlalchemy.orm import Session
from database import SessionLocal, engine
import models
import datetime
from passlib.context import CryptContext

def get_password_hash(password):
    return password # Simplified for testing

def seed_db():
    db = SessionLocal()
    
    # Check if we already have data
    if db.query(models.User).first():
        print("Database already seeded. Cleaning for fresh seed...")
        db.query(models.Alert).delete()
        db.query(models.AccessLog).delete()
        db.query(models.Vehicle).delete()
        db.query(models.User).delete()
        db.commit()

    print("Seeding database with full test information...")
    
    # 1. Create Users (Administrators and Regular Users)
    users = [
        models.User(
            username="admin_principal",
            email="admin@okovision.com",
            hashed_password=get_password_hash("admin_password"),
            is_admin=True
        ),
        models.User(
            username="soporte_tecnico",
            email="soporte@okovision.com",
            hashed_password=get_password_hash("soporte2024"),
            is_admin=True
        ),
        models.User(
            username="juan_perez",
            email="juan@gmail.com",
            hashed_password=get_password_hash("password123"),
            is_admin=False
        ),
        models.User(
            username="maria_garcia",
            email="maria@outlook.com",
            hashed_password=get_password_hash("secure_password"),
            is_admin=False
        ),
        models.User(
            username="carlos_rodriguez",
            email="carlos.rod@empresa.com",
            hashed_password=get_password_hash("carlos789"),
            is_admin=False
        ),
        models.User(
            username="ana_martinez",
            email="ana.mtz@gmail.com",
            hashed_password=get_password_hash("ana_pass"),
            is_admin=False
        )
    ]
    
    db.add_all(users)
    db.commit()
    for u in users:
        db.refresh(u)
    
    # Map users for easy access
    u_map = {u.username: u for u in users}
    
    # 2. Create Vehicles
    vehicles = [
        models.Vehicle(plate="ABC-123", brand="Toyota", model="Corolla", color="Gris Metálico", owner_id=u_map["juan_perez"].id),
        models.Vehicle(plate="XYZ-789", brand="Honda", model="Civic", color="Blanco Perla", owner_id=u_map["maria_garcia"].id),
        models.Vehicle(plate="OKO-001", brand="Tesla", model="Model 3", color="Negro Mate", owner_id=u_map["admin_principal"].id),
        models.Vehicle(plate="BMW-456", brand="BMW", model="X5", color="Azul Marino", owner_id=u_map["carlos_rodriguez"].id),
        models.Vehicle(plate="AUD-101", brand="Audi", model="A4", color="Rojo Brillante", owner_id=u_map["ana_martinez"].id),
        models.Vehicle(plate="FRD-202", brand="Ford", model="F-150", color="Plata", owner_id=u_map["juan_perez"].id), # Juan has two cars
    ]
    
    db.add_all(vehicles)
    db.commit()
    
    # 3. Create Access Logs (Recent history)
    now = datetime.datetime.utcnow()
    logs = [
        models.AccessLog(user_id=u_map["juan_perez"].id, vehicle_plate="ABC-123", access_type="ENTRY", is_authorized=True, access_time=now - datetime.timedelta(hours=5)),
        models.AccessLog(user_id=u_map["maria_garcia"].id, vehicle_plate="XYZ-789", access_type="ENTRY", is_authorized=True, access_time=now - datetime.timedelta(hours=4)),
        models.AccessLog(user_id=u_map["carlos_rodriguez"].id, vehicle_plate="BMW-456", access_type="ENTRY", is_authorized=True, access_time=now - datetime.timedelta(hours=3)),
        models.AccessLog(user_id=u_map["juan_perez"].id, vehicle_plate="ABC-123", access_type="EXIT", is_authorized=True, access_time=now - datetime.timedelta(hours=2)),
        models.AccessLog(user_id=u_map["admin_principal"].id, vehicle_plate="OKO-001", access_type="ENTRY", is_authorized=True, access_time=now - datetime.timedelta(hours=1)),
        models.AccessLog(user_id=u_map["ana_martinez"].id, vehicle_plate="AUD-101", access_type="ENTRY", is_authorized=True, access_time=now - datetime.timedelta(minutes=30)),
    ]
    
    db.add_all(logs)
    
    # 4. Create Alerts
    alerts = [
        models.Alert(title="Placa No Reconocida", description="Vehículo intentó ingresar con placa: GHI-456. Acceso Denegado.", severity="HIGH"),
        models.Alert(title="Sistema Actualizado", description="Se completó la migración de base de datos Postgres con éxito.", severity="LOW"),
        models.Alert(title="Cámara Offline", description="La cámara de la Zona Norte no responde al ping.", severity="CRITICAL"),
        models.Alert(title="Acceso Fuera de Horario", description="El usuario carlos_rodriguez ingresó a las 03:00 AM.", severity="MEDIUM"),
    ]
    
    db.add_all(alerts)
    
    db.commit()
    print("Database seeded successfully with 6 users and 6 vehicles!")
    db.close()

if __name__ == "__main__":
    seed_db()
