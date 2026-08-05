#!/bin/bash
set +e
BASE="http://localhost/mobile-api"

# Login
LOGIN=$(curl -s -X POST "$BASE/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@okovision.com","password":"12345678"}')
TOKEN=$(echo $LOGIN | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('access_token','__FAIL__'))" 2>/dev/null)
if [ "$TOKEN" = "__FAIL__" ]; then
  echo "LOGIN FAIL: $LOGIN"
  exit 1
fi

echo "Token OK (${#TOKEN} chars)"
echo ""

probar() {
  local method=$1
  local path=$2
  local body=$3
  if [ -n "$body" ]; then
    RESP=$(curl -s -w "\nHT=%{http_code}=" \
      -X $method "$BASE$path" \
      -H "Authorization: Bearer $TOKEN" \
      -H "Content-Type: application/json" \
      -d "$body" 2>&1)
  else
    RESP=$(curl -s -w "\nHT=%{http_code}=" \
      -X $method "$BASE$path" \
      -H "Authorization: Bearer $TOKEN" 2>&1)
  fi
  HTTP_CODE=$(echo "$RESP" | grep -oP 'HT=\K[0-9]+' | tail -1)
  BODY=$(echo "$RESP" | sed '$d' | head -c 200 | tr '\n' ' ')
  case "$HTTP_CODE" in
    200|201|202|204) ICONO="✅ OK  " ;;
    404) ICONO="❌ 404 NO EXISTE   " ;;
    422) ICONO="⚠️ 422 VALIDACION  " ;;
    500|502|503) ICONO="🔥 5xx ERROR  " ;;
    307|301|302) ICONO="🔀 3xx REDIRECT " ;;
    401) ICONO="🔒 401 UNAUTH    " ;;
    *) ICONO="❔ $HTTP_CODE ???      " ;;
  esac
  printf "%s %-6s %-25s -> body=%s\n" "$ICONO" "$method" "$path" "$BODY"
}

echo "--- AUTH ---"
probar "POST" "/auth/login" '{"email":"admin@okovision.com","password":"12345678"}'
probar "GET"  "/auth/me"

echo ""
echo "--- VEHICULOS ---"
probar "GET"   "/vehiculos"
probar "POST"  "/vehiculos" '{"plate":"TEST-AUD","marca":"Prueba","modelo":"Audit","anio":2026,"color":"Rojo","tipo":"auto","owner_id":1,"photo_uri":null}'
probar "GET"   "/vehiculos/tipos"
probar "GET"   "/vehiculos/1"
probar "PATCH" "/vehiculos/1" '{"marca":"Actualizado","color":"Negro"}'
probar "PATCH" "/vehiculos/1/" '{"color":"PruebaSlash"}'

echo ""
echo "--- USUARIOS ---"
probar "GET"   "/usuarios/me"
probar "PATCH" "/usuarios/1" '{"password":"12345678"}'

echo ""
echo "--- ACCESOS ---"
probar "GET"  "/accesos"
probar "POST" "/accesos" '{"user_id":1,"type":"entry","method":"qr","notes":"Prueba auditoria OKO","timestamp":"2026-08-05T17:00:00Z"}'

echo ""
echo "--- ALERTAS ---"
probar "GET"   "/alerts"
probar "GET"   "/alerts/1"
probar "PATCH" "/alerts/1" '{"notes":"Actualizacion audit","status":"PENDING"}'

echo ""
echo "--- QR ---"
probar "GET" "/qr/payload"
