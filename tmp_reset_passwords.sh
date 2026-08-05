#!/bin/bash
# Actualizar hash de password de TODOS los usuarios a 12345678 usando bcrypt de Python (passlib)
# Tomamos como referencia el hash del admin que SI funciona: $2y$10$... 
# O mejor: generamos hashes desde el propio FastAPI container (tiene passlib/bcrypt instalado)

echo "=== RESET PASSWORDS A 12345678 ==="
docker exec oko_prod_api_1 python3 -c "
from passlib.context import CryptContext
import psycopg2
import os

ctx = CryptContext(schemes=['bcrypt'], deprecated='auto')
new_hash = ctx.hash('12345678')
print('Nuevo hash generado:', new_hash[:40]+'...')

conn = psycopg2.connect(
    host=os.environ.get('DB_HOST','postgres_db'),
    database=os.environ.get('DB_NAME','oko_vision'),
    user=os.environ.get('DB_USER','oko_admin'),
    password=os.environ.get('DB_PASS', 'CambiaEstaContraseña_2026_SuperSegura!')
)
cur = conn.cursor()
cur.execute('UPDATE usuarios SET password = %s', (new_hash,))
print('Filas afectadas:', cur.rowcount)
conn.commit()
cur.close()
conn.close()
print('OK: todos los usuarios ahora tienen password 12345678')
" 2>&1

echo ""
echo "=== VERIFICACION LOGIN DE TODOS ==="
for pair in \
  "admin@okovision.com" \
  "maria@outlook.com" \
  "car@okovision.com" \
  "test_us@okovision.com" \
  "axel@okovision.com" \
  "juan@gmail.com" \
  "test@okovision.com"
do
  status=$(curl -s -o /dev/null -w '%{http_code}' 'http://localhost/mobile-api/auth/login' \
    -X POST \
    -H 'Content-Type: application/json' \
    -d "{\"email\":\"$pair\",\"password\":\"12345678\"}")
  if [ "$status" = "200" ]; then
    echo "✅ 200 $pair : 12345678"
  else
    echo "❌ $status $pair : 12345678"
  fi
done
