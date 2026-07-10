from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from app.data.database import Usuario, Persona
from app.security.auth import get_password_hash, verify_password
import os

# Usamos el nombre del servicio Docker para conectarse desde el contenedor
DATABASE_URL = "postgresql://oko_admin:oko_password@postgres_db:5432/oko_vision"

engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

def reset_passwords():
    db = SessionLocal()
    print("Usuarios actuales en la base de datos:")
    print("-" * 80)
    
    usuarios = db.query(Usuario).all()
    
    for u in usuarios:
        persona = db.query(Persona).filter(Persona.id == u.id_persona).first()
        
        # Establecemos una contraseña fija para pruebas: "123456"
        nueva_contrasena = "123456"
        u.password = get_password_hash(nueva_contrasena)
        
        print(f"ID Usuario: {u.id}")
        print(f"Identificador: {u.identificador}")
        print(f"Email: {persona.mail if persona else 'N/A'}")
        print(f"Nueva contraseña: {nueva_contrasena}")
        print("-" * 80)
    
    db.commit()
    db.close()
    print("Contraseñas actualizadas exitosamente!")

if __name__ == "__main__":
    reset_passwords()
