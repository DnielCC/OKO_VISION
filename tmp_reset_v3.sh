#!/bin/bash
echo "=== RESET PASSWORDS VIA DB CONTAINER DIRECTO ==="
# Generar hash con API container
NEW_HASH=$(docker exec oko_prod_api_1 python3 -c "
import bcrypt
print(bcrypt.hashpw(b'12345678', bcrypt.gensalt(rounds=10)).decode())
" 2>&1 | tail -1)
echo "Hash = $NEW_HASH"

# Actualizar mediante psql desde el contenedor postgres (confianza local)
docker exec oko_prod_db psql -U oko_admin -d oko_vision -c "UPDATE usuarios SET password = '$NEW_HASH';"

echo ""
echo "=== VERIFICACION LOGIN (HTTP mobile-api) ==="
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
    echo "❌ $status $pair -> $(cat /tmp/out.json | head -c 120)"
  fi
done
