#!/bin/bash
set +e

echo "=== 1. LOGIN para obtener token ==="
LOGIN=$(curl -s -X POST http://localhost/mobile-api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@okovision.com","password":"12345678"}')
echo "Login: $(echo $LOGIN | head -c 200)"
TOKEN=$(echo $LOGIN | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('access_token',''))")
if [ -z "$TOKEN" ]; then
  echo "❌ NO SE OBTUVO TOKEN"
  exit 1
fi
echo "✅ Token OK"

echo ""
echo "=== 2. LISTAR VEHICULOS (GET /mobile-api/vehiculos/) ==="
RESP=$(curl -s -w "\n---HTTP:%{http_code}---" http://localhost/mobile-api/vehiculos/ \
  -H "Authorization: Bearer $TOKEN")
echo "$RESP"

echo ""
echo "=== 3. VERIFICAR VEHICULO_ID 1 EXISTE (GET /mobile-api/vehiculos/1) ==="
RESP=$(curl -s -w "\n---HTTP:%{http_code}---" http://localhost/mobile-api/vehiculos/1 \
  -H "Authorization: Bearer $TOKEN")
echo "$RESP"

echo ""
echo "=== 4. SIMULAR LO QUE ENVIA LA APP AL EDITAR (PATCH /mobile-api/vehiculos/1/) ==="
echo "Payload con: plate, marca, modelo, anio, color, tipo, owner_id, photo_uri"
curl -s -X PATCH "http://localhost/mobile-api/vehiculos/1/" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: OKO-Vision-Mobile" \
  -d '{
    "plate": "VEH-001",
    "marca": "Supra",
    "modelo": "Corolla",
    "anio": 2022,
    "color": "Gris",
    "tipo": "auto",
    "owner_id": 1,
    "photo_uri": null
  }' -w "\n---HTTP:%{http_code}---\n"

echo ""
echo "=== 5. PATCH SIN SLASH FINAL /mobile-api/vehiculos/1 ==="
curl -s -X PATCH "http://localhost/mobile-api/vehiculos/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "plate": "VEH-001",
    "marca": "Toyota",
    "modelo": "Corolla",
    "anio": 2022,
    "color": "Gris",
    "tipo": "auto",
    "owner_id": 1,
    "photo_uri": null
  }' -w "\n---HTTP:%{http_code}---\n"

echo ""
echo "=== 6. PUT /mobile-api/vehiculos/1 (por si la app cambiara a PUT) ==="
curl -s -X PUT "http://localhost/mobile-api/vehiculos/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "plate": "VEH-001",
    "marca": "Toyota",
    "modelo": "Corolla",
    "anio": 2022,
    "color": "Gris",
    "tipo": "auto",
    "owner_id": 1
  }' -w "\n---HTTP:%{http_code}---\n"

echo ""
echo "=== 7. POST CREAR VEHICULO ==="
curl -s -X POST "http://localhost/mobile-api/vehiculos/" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "plate": "VEH-999",
    "marca": "Prueba",
    "modelo": "Test",
    "anio": 2024,
    "color": "Negro",
    "tipo": "auto",
    "owner_id": 1,
    "photo_uri": null
  }' -w "\n---HTTP:%{http_code}---\n"

echo ""
echo "=== 8. LOGS DE ERRORES RECIENTES (API 1) ==="
docker logs --tail 20 oko_prod_api_1 2>&1 | grep -i -A3 "error\|traceback\|validation" || echo "(sin errores)"
