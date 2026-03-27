from sqlalchemy.orm import Session
from app.data.db import SessionLocal, engine
from app.data import database
import datetime

def seed_new_db():
    db = SessionLocal()
    
    print("Cleaning and seeding new structure...")
    db.query(database.Acceso).delete()
    db.query(database.PersonaVehiculo).delete()
    db.query(database.Usuario).delete()
    db.query(database.Visitante).delete()
    db.query(database.Persona).delete()
    db.query(database.Vehiculo).delete()
    db.query(database.Rol).delete()
    db.query(database.Estatus).delete()
    db.query(database.Puerta).delete()
    db.query(database.Dispositivo).delete()
    db.commit()

    # 1. Roles
    roles = [
        database.Rol(nombre="Administrador"),
        database.Rol(nombre="Estudiante"),
        database.Rol(nombre="Docente"),
        database.Rol(nombre="Personal Administrativo")
    ]
    db.add_all(roles)
    db.commit()
    
    # 2. Estatus
    estatus = [
        database.Estatus(nombre="Activo"),
        database.Estatus(nombre="Inactivo"),
        database.Estatus(nombre="Suspendido")
    ]
    db.add_all(estatus)
    db.commit()

    # 3. Personas
    personas = [
        database.Persona(nombre="Admin", apellidos="Oko", mail="admin@okovision.com", telefono="1234567890"),
        database.Persona(nombre="Juan", apellidos="Perez", mail="juan@gmail.com", telefono="0987654321"),
        database.Persona(nombre="Maria", apellidos="Garcia", mail="maria@outlook.com", telefono="1122334455")
    ]
    db.add_all(personas)
    db.commit()
    for p in personas: db.refresh(p)

    # 4. Usuarios
    usuarios = [
        database.Usuario(id_persona=personas[0].id, id_rol=roles[0].id, identificador="admin_principal"),
        database.Usuario(id_persona=personas[1].id, id_rol=roles[1].id, identificador="juan_perez"),
        database.Usuario(id_persona=personas[2].id, id_rol=roles[1].id, identificador="maria_garcia")
    ]
    db.add_all(usuarios)
    db.commit()

    # 5. Vehículos
    vehiculos = [
        database.Vehiculo(marca="Toyota", modelo="Corolla", anio=2022, color="Gris", tipo="auto"),
        database.Vehiculo(marca="Honda", modelo="Civic", anio=2021, color="Blanco", tipo="auto"),
        database.Vehiculo(marca="Tesla", modelo="Model 3", anio=2023, color="Negro", tipo="auto")
    ]
    db.add_all(vehiculos)
    db.commit()
    for v in vehiculos: db.refresh(v)

    # 6. Relación Persona-Vehículo
    relaciones = [
        database.PersonaVehiculo(id_persona=personas[1].id, id_vehiculo=vehiculos[0].id, id_estatus=estatus[0].id),
        database.PersonaVehiculo(id_persona=personas[2].id, id_vehiculo=vehiculos[1].id, id_estatus=estatus[0].id),
        database.PersonaVehiculo(id_persona=personas[0].id, id_vehiculo=vehiculos[2].id, id_estatus=estatus[0].id)
    ]
    db.add_all(relaciones)
    db.commit()

    # 7. Puertas y Dispositivos
    puerta = database.Puerta(nombre="Entrada Principal")
    dispositivo = database.Dispositivo(nombre="Cámara 1")
    db.add_all([puerta, dispositivo])
    db.commit()
    db.refresh(puerta)
    db.refresh(dispositivo)

    # 8. Accesos
    accesos = [
        database.Acceso(id_persona=personas[1].id, id_vehiculo=vehiculos[0].id, id_puerta=puerta.id, id_dispositivo=dispositivo.id, tipo_acceso='P', resultado='p', metodo='credencial'),
        database.Acceso(id_persona=personas[2].id, id_vehiculo=vehiculos[1].id, id_puerta=puerta.id, id_dispositivo=dispositivo.id, tipo_acceso='V', resultado='p', metodo='credencial')
    ]
    db.add_all(accesos)
    db.commit()

    print("New structure seeded successfully!")
    db.close()

if __name__ == "__main__":
    seed_new_db()
