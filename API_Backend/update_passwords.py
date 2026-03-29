import os
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from app.data.database import Usuario, Persona
from app.security.auth import get_password_hash

# DATABASE_URL = os.getenv("DATABASE_URL", "postgresql://oko_admin:oko_password@localhost:5433/oko_vision")
# If running inside docker, use postgres_db:5432. Since we'll run from host usually or docker exec:
DATABASE_URL = "postgresql://oko_admin:oko_password@postgres_db:5432/oko_vision"

engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

def update_passwords():
    db = SessionLocal()
    usuarios = db.query(Usuario).all()
    
    for u in usuarios:
        if not u.password:
            # Asignar identificador como password inicial
            new_password = u.identificador
            # Si el identificador es muy corto (menos de 4), usar '1234'
            if len(new_password) < 4:
                new_password = "1234" + new_password
                
            u.password = get_password_hash(new_password)
            print(f"Usuario {u.identificador} actualizado con password: {new_password}")
            
    try:
        # Intenta agregar la columna si no existe (algunos dialectos soportan esto, pero en Postgres es manual o SQLAlchemy migrate)
        # Aquí asumimos que SQLAlchemy Base.metadata.create_all() en main.py o Alembic (si hubiera) lo maneja.
        # En caso de error de columna, el DB schema string cambia, así que hacemos un query raw para ALTER TABLE si falla.
        pass
    except Exception:
        pass

    try:
        db.commit()
    except Exception as e:
        print("Error al aplicar cambios (quizás la columna 'password' no ha sido creada aún):", e)
        db.rollback()

    db.close()

if __name__ == "__main__":
    update_passwords()
