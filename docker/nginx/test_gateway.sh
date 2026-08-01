#!/usr/bin/env bash
set -u
echo '=== Gateway ==='
echo ''
echo '=== FastAPI via HTTPS ==='
curl -skL -X POST -w '\nSTATUS:%{http_code}\n' \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@okovision.com","password":"12345678"}' \
  https://127.0.0.1/api/auth/login > /tmp/login.json 2>/dev/null
cat /tmp/login.json | grep -oE '"access_token":"[^"]+"' | head -c 80 ; echo ''
echo 'Last line:' $(tail -1 /tmp/login.json)
TOK=$(cat /tmp/login.json | sed '/^STATUS:/d; /^\s*$/d' | tr -d '\n' | sed -n 's/.*"access_token":"\([^"]*\)".*/\1/p')
echo "Token length=${#TOK}"

if [ -n "$TOK" ]; then
  echo ''
  echo '=== GET /api/usuarios HTTPS ==='
  curl -skL -w '\nSTATUS:%{http_code}\n' \
    -H "Authorization: Bearer $TOK" \
    https://127.0.0.1/api/usuarios/ > /tmp/users.json 2>/dev/null
  head -c 300 /tmp/users.json ; echo ''
  echo 'Last line:' $(tail -1 /tmp/users.json)
fi

echo ''
echo '=== Laravel Admin /login HTTPS ==='
curl -skL -w '\nSTATUS:%{http_code}\n' https://127.0.0.1/login > /tmp/l.html 2>/dev/null
echo "Login page length=$(wc -c < /tmp/l.html)"
grep -oE 'name="_token"[^>]+' /tmp/l.html | head -2

echo ''
echo '=== Flask Mobile HTTPS ==='
curl -skL -X POST -w '\nSTATUS:%{http_code}\n' \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@okovision.com","password":"12345678"}' \
  https://127.0.0.1/mobile-api/auth/login > /tmp/flogin.json 2>/dev/null
echo 'Resp length:' $(wc -c < /tmp/flogin.json)
head -c 400 /tmp/flogin.json ; echo ''
echo 'Last line:' $(tail -1 /tmp/flogin.json)

echo ''
echo '=== Monitoreo ==='
curl -skL -o /dev/null -w 'Prometheus /-/healthy: %{http_code}\n' https://127.0.0.1/prometheus/-/healthy
curl -skL -o /dev/null -w 'Grafana /api/health: %{http_code}\n' https://127.0.0.1/grafana/api/health
