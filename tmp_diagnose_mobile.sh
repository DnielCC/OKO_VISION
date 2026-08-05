#!/bin/bash
set -e

echo "=== 1. LOGIN TEST ==="
LOGIN_RESP=$(curl -s -X POST http://localhost/mobile-api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@okovision.com","password":"12345678"}')
echo "Login response: $(echo $LOGIN_RESP | head -c 200)"

TOKEN=$(echo $LOGIN_RESP | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('access_token','NO_TOKEN'))")
echo "Token (first 60 chars): ${TOKEN:0:60}..."

echo ""
echo "=== 2. LISTAR VEHICULOS (GET /auto) ==="
curl -s -w "\nHTTP_STATUS: %{http_code}\n" http://localhost/mobile-api/auto \
  -H "Authorization: Bearer $TOKEN" 2>&1 | head -c 500

echo ""
echo "=== 3. UN SOLO VEHICULO (si existe ID 1) ==="
curl -s -w "\nHTTP_STATUS: %{http_code}\n" http://localhost/mobile-api/auto/1 \
  -H "Authorization: Bearer $TOKEN" 2>&1 | head -c 500

echo ""
echo "=== 4. TIPOS DE VEHICULO ==="
curl -s -w "\nHTTP_STATUS: %{http_code}\n" http://localhost/mobile-api/auto/tipos \
  -H "Authorization: Bearer $TOKEN" 2>&1 | head -c 500

echo ""
echo "=== 5. LOGS API 1 (errores últimos 30) ==="
docker logs --tail 30 oko_prod_api_1 2>&1 | grep -i -E "error|exception|traceback" || echo "(No se encontraron errores en logs filtrados)"

echo ""
echo "=== 6. LOGS API 2 (errores últimos 30) ==="
docker logs --tail 30 oko_prod_api_2 2>&1 | grep -i -E "error|exception|traceback" || echo "(No se encontraron errores en logs filtrados)"

echo ""
echo "=== 7. NGINX ACCESS LOG - POST /auto (últimos 20) ==="
docker logs --tail 100 oko_prod_gateway 2>&1 | grep -i "auto" | tail -20 || echo "(Sin entradas para /auto en logs)"
