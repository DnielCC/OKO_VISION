#!/bin/bash
echo "=== RESET PASSWORDS A 12345678 CON PYTHON BCRYPT DIRECTO ==="
docker exec oko_prod_api_1 python3 -c "
import bcrypt, os, psycopg2

password = b'12345678'
new_bytes = bcrypt.hashpw(password, bcrypt.gensalt(rounds=10))
new_hash = new_bytes.decode('utf-8')
print('Hash generado:', new_hash)

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
print('OK: Contraseñas reseteadas a 12345678')
" 2>&1

echo ""
echo "=== VERIFICACION LOGIN ==="
for pair in \
  "admin@okovision.com" \
  "maria@outlook.com" \
  "car@okovision.com" \
  "test_us@okovision.com" \
  "axel@okovision.com" \
  "juan@gmail.com" \
  "test@okovision.com"
do
  status=$(curl -s -o /tmp/out.json -w '%{http_code}' 'http://localhost/mobile-api/auth/login' \
    -X POST \
    -H 'Content-Type: application/json' \
    -d "{\"email\":\"$pair\",\"password\":\"12345678\"}")
  if [ "$status" = "200" ]; then
    echo "✅ 200 $pair"
  else
    echo "❌ $status $pair -> $(cat /tmp/out.json | head -c 150)"
  fi
done
