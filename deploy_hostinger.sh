#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${ENV_FILE:-$PROJECT_DIR/.env}"
COMPOSE_FILE="${COMPOSE_FILE:-$PROJECT_DIR/docker-compose.prod.yml}"

echo "[OKO] Proyecto: $PROJECT_DIR"
echo "[OKO] Env file: $ENV_FILE"
echo "[OKO] Compose file: $COMPOSE_FILE"

if ! command -v docker >/dev/null 2>&1; then
  echo "[OKO][ERROR] Docker no está instalado."
  exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
  echo "[OKO][ERROR] Docker Compose plugin no está disponible."
  exit 1
fi

if [ ! -f "$ENV_FILE" ]; then
  echo "[OKO][ERROR] No existe el archivo .env."
  echo "[OKO] Crea uno con:"
  echo "  cp .env.prod.example .env"
  exit 1
fi

if [ ! -f "$COMPOSE_FILE" ]; then
  echo "[OKO][ERROR] No existe docker-compose.prod.yml."
  exit 1
fi

echo "[OKO] Validando configuración..."
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" config >/dev/null

echo "[OKO] Construyendo e iniciando servicios..."
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --build

echo "[OKO] Estado actual:"
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps

echo "[OKO] Prueba rápida del gateway:"
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" exec -T nginx_gateway wget -qO- http://localhost/health || true

echo
echo "[OKO] Despliegue completado."
echo "[OKO] URLs esperadas:"
echo "  Admin:       \$APP_URL/"
echo "  Usuario:     \$APP_URL/flask/"
echo "  API:         \$APP_URL/api/health"
echo "  API móvil:   \$APP_URL/mobile-api/"
echo "  Grafana:     \$APP_URL/grafana/"
