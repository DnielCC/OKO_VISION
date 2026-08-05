#!/bin/bash
set -e

echo "=== TEST 1: ADMIN OKO (HTTP mobile-api) ==="
curl -s 'http://localhost/mobile-api/auth/login' \
  -X POST \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@okovision.com","password":"12345678"}' \
  -w '\nSTATUS:%{http_code}\n'

echo ""
echo "=== TEST 2: JUAN PEREZ ==="
curl -s 'http://localhost/mobile-api/auth/login' \
  -X POST \
  -H 'Content-Type: application/json' \
  -d '{"email":"juan@gmail.com","password":"12345678"}' \
  -w '\nSTATUS:%{http_code}\n'

echo ""
echo "=== TEST 3: PASSWORD INCORRECTA (control) ==="
curl -s 'http://localhost/mobile-api/auth/login' \
  -X POST \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@okovision.com","password":"PASSWORD_MAL"}' \
  -w '\nSTATUS:%{http_code}\n'

echo ""
echo "=== TEST 4: HEALTH mobile-api ==="
curl -s 'http://localhost/mobile-api/health' -w '\nSTATUS:%{http_code}\n'
