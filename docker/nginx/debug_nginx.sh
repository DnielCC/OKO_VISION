#!/usr/bin/env bash
set -u
echo '=== 1) FastAPI cluster /health direct ==='
curl -s -o /tmp/x.json -w 'STATUS:%{http_code}\n' http://api_backend:8000/health
echo '  body:' ; cat /tmp/x.json ; echo ''
echo ''
echo '=== 2) Backend DNS & FastAPI direct ==='
for i in api_backend:8000 api_backend_2:8000; do
  echo -n "  curl -> http://$i/health : "
  curl -s -o /dev/null -w '%{http_code}\n' "http://$i/health"
done
echo ''
echo '=== 3) Nginx internal HTTP /api/health & /api/auth/login ==='
curl -s -o /tmp/y.json -w '/api/health STATUS:%{http_code}\n' http://127.0.0.1/api/health
echo '  body:' ; cat /tmp/y.json ; echo ''
echo ''
echo '=== 4) Nginx syntax ==='
nginx -t 2>&1
echo ''
echo '=== 5) Via proxy_pass: /api/health to upstream ==='
curl -s -o /tmp/z.json -w 'STATUS:%{http_code}\n' \
  --resolve api_cluster:80:127.0.0.1 \
  -H 'Host: api_cluster' \
  http://127.0.0.1/api/health
cat /tmp/z.json ; echo ''
