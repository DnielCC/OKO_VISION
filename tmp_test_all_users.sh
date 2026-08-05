#!/bin/bash
echo "=== TEST LOGINS EXISTENTES ==="
for pair in \
  "admin@okovision.com:12345678" \
  "maria@outlook.com:12345678" \
  "car@okovision.com:12345678" \
  "test_us@okovision.com:12345678" \
  "axel@okovision.com:12345678" \
  "juan@gmail.com:12345678" \
  "test@okovision.com:12345678" \
  "cano@okovision.com:12345678" \
  "axe@okovision.com:12345678"
do
  email=$(echo "$pair" | cut -d: -f1)
  pass=$(echo "$pair" | cut -d: -f2)
  status=$(curl -s -o /dev/null -w '%{http_code}' 'http://localhost/mobile-api/auth/login' \
    -X POST \
    -H 'Content-Type: application/json' \
    -d "{\"email\":\"$email\",\"password\":\"$pass\"}")
  if [ "$status" = "200" ]; then
    echo "✅ 200 $email : $pass"
  else
    echo "❌ $status $email : $pass"
  fi
done
