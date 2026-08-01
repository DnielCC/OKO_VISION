#!/bin/bash
set -e

SSL_DIR="/etc/nginx/ssl"
CERT_FILE="$SSL_DIR/okovision.crt"
KEY_FILE="$SSL_DIR/okovision.key"

mkdir -p "$SSL_DIR"

# Validar si el cert ya existente es correcto (PEM valido), si no regenerar.
REGEN=1
if [ -f "$CERT_FILE" ] && [ -s "$CERT_FILE" ] && openssl x509 -in "$CERT_FILE" -noout >/dev/null 2>&1; then
    REGEN=0
fi

if [ "$REGEN" = "1" ]; then
    echo "[OKO] Generando certificado SSL autofirmado..."
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$KEY_FILE" \
        -out "$CERT_FILE" \
        -subj "/C=MX/ST=CDMX/L=Mexico/O=OKO VISION/OU=IT/CN=localhost" \
        -addext "subjectAltName=DNS:localhost,DNS:*.localhost,IP:127.0.0.1,IP:10.0.2.2,IP:172.17.0.1" 2>/dev/null

    chmod 600 "$KEY_FILE"
    chmod 644 "$CERT_FILE"
    echo "[OKO] Certificados SSL generados correctamente."
else
    echo "[OKO] Certificados SSL válidos encontrados. Saltando generación."
fi

# Ceder al CMD definido en el Dockerfile (nginx -g daemon off)
exec "$@"
