#!/bin/bash
# AUDITORIA EXHAUSTIVA: Endpoints requeridos por la App Móvil OKO VISION
set +e

BASE="http://localhost/mobile-api"
echo "=== AUDITORIA ENDPOINTS APP MOVIL (mobile-api) ==="
echo ""

# Obtener token
echo "[PASO 0] Login..."
LOGIN=$(curl -s -X POST "$BASE/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@okovision.com","password":"12345678"}')
TOKEN=$(echo $LOGIN | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('access_token','NO_TOKEN'))" 2>/dev/null)
if [ "$TOKEN" = "NO_TOKEN" ] || [ -z "$TOKEN" ]; then
  echo "❌ LOGIN FALLO: $LOGIN"
  exit 1
fi
echo "✅ Token obtenido"
echo ""

echo "| Endpoint | Método | HTTP | Estado | Observaciones |"
echo "|---|---|---|---|---|"

probar() {
  local method=$1
  local path=$2
  local body=$3
  local extra=$4
  if [ -n "$body" ]; then
    RESP=$(curl -s -w "\n---HTTP:%{http_code}---" \
      -X $method "$BASE$path" \
      -H "Authorization: Bearer $TOKEN" \
      -H "Content-Type: application/json" \
      -d "$body" 2>&1)
  else
    RESP=$(curl -s -w "\n---HTTP:%{http_code}---" \
      -X $method "$BASE$path" \
      -H "Authorization: Bearer $TOKEN" 2>&1)
  fi
  HTTP_CODE=$(echo "$RESP" | tail -1 | grep -oP 'HTTP:\K\d+' | tail -1)
  BODY=$(echo "$RESP" | sed '$d' | head -c 300 | tr -d '\n')
  ICONO="✅"
  ESTADO="OK"
  OBS="$extra"
  if [ "$HTTP_CODE" = "404" ]; then
    ICONO="❌"
    ESTADO="404 - No Existe"
    OBS="DEBE CREARSE EN EL BACKEND | $BODY"
  elif [ "$HTTP_CODE" = "422" ]; then
    ICONO="⚠️"
    ESTADO="422 - Validación"
    OBS="Body no coincide con schema: $BODY"
  elif [ "$HTTP_CODE" = "500" ] || [ "$HTTP_CODE" = "502" ] || [ "$HTTP_CODE" = "503" ]; then
    ICONO="🔥"
    ESTADO="$HTTP_CODE - Server Error"
    OBS=$BODY
  elif [ "$HTTP_CODE" = "307" ] || [ "$HTTP_CODE" = "301" ] || [ "$HTTP_CODE" = "302" ]; then
    ICONO="🔀"
    ESTADO="$HTTP_CODE - Redirect"
    OBS="Peligroso para Axios + móvil"
  elif [ "$HTTP_CODE" = "401" ]; then
    ICONO="🔒"
    ESTADO="401 - No Autorizado"
    OBS=$BODY
  elif [ "$HTTP_CODE" = "400" ]; then
    ICONO="⚠️"
    ESTADO="400 - Bad Request"
    OBS="$BODY"
  fi
  echo "| $path | $method | $HTTP_CODE | $ICONO $ESTADO | $OBS |"
}

# AUTH
echo ""
echo "=== AUTH ==="
probar "POST" "/auth/login" '{"email":"admin@okovision.com","password":"12345678"}' "Login funcionando"
probar "GET" "/auth/me" "" "Debe retornar datos del usuario (nombre, apellidos, rol...)"

# VEHICULOS
echo ""
echo "=== VEHICULOS / MIS CARROS ==="
probar "GET" "/vehiculos" "" "Listar todos los vehículos"
probar "POST" "/vehiculos" '{"plate":"TEST-999","marca":"Test","modelo":"Modelo","anio":2024,"color":"Gris","tipo":"auto","owner_id":1,"photo_uri":null}' "CREAR VEHICULO (lo que usa el móvil para AGREGAR)"
probar "GET" "/vehiculos/1" "" "Detalles de vehiculo 1"
probar "PATCH" "/vehiculos/1" '{"marca":"TestActualizado"}' "ACTUALIZAR VEHICULO (editar carro)"
probar "PATCH" "/vehiculos/1/" '{"marca":"ConSlashFinal"}' "Prueba con slash final (debe funcionar)"
probar "DELETE" "/vehiculos/4" "" "Eliminar vehículo (ID 4 creado por test anterior)"
probar "GET" "/vehiculos/tipos" "" "Opcional: tipos permitidos"

# USUARIOS
echo ""
echo "=== USUARIOS / PERFIL ==="
probar "PATCH" "/usuarios/1" '{"password":"12345678Nueva!"}' "CAMBIAR CONTRASEÑA (ChangePasswordScreen)"
probar "GET" "/usuarios/me" "" "Obtener perfil propio (opcional)"

# ACCESOS
echo ""
echo "=== ACCESOS / HISTORIAL ==="
probar "GET" "/accesos" "" "Listar historial de accesos (HistoryScreen, HomeScreen)"
probar "GET" "/accesos/" "" "Con slash final"
probar "POST" "/accesos" '{"user_id":1,"type":"entry","method":"qr","notes":"Test auditoria","timestamp":"2026-08-05T17:00:00Z"}' "REGISTRAR ACCESO (QRScannerScreen)"
probar "POST" "/accesos/" '{"user_id":1,"type":"exit","method":"qr","notes":"Con slash"}' "POST con slash"

# ALERTAS
echo ""
echo "=== ALERTAS ==="
probar "GET" "/alerts" "" "Listar alertas (HomeScreen)"
probar "GET" "/alerts/" "" "Con slash final"
probar "PATCH" "/alerts/1" '{"notes":"Nota actualizada desde auditoria"}' "Guardar notas (AlertDetailScreen)"

# QR
echo ""
echo "=== QR ==="
probar "GET" "/qr/payload" "" "Generar payload QR firmado (QRScreen)"

# USERS (Laravel-compat routes)
echo ""
echo "=== OTROS (Opcionales) ==="
probar "GET" "/users/me" "" "ProfileScreen si existe"
probar "GET" "/people/me" "" "Persona info"
probar "GET" "/personas/1" "" "Detalle persona"
