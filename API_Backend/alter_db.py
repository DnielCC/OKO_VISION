import psycopg2
import sys

try:
    # Asume ejecución local (fuera de docker)
    conn = psycopg2.connect("dbname=oko_vision user=oko_admin password=oko_password host=localhost port=5433")
    cur = conn.cursor()
    
    # Agregar columna
    cur.execute("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS password VARCHAR(255);")
    conn.commit()
    print("Columna 'password' agregada exitosamente a la tabla 'usuarios'.")
    cur.close()
    conn.close()
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
