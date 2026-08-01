FROM nginx:1.27-alpine

# Instalar utilidades DURANTE BUILD (no en runtime entrypoint - evita colgar)
RUN apk add --no-cache openssl wget curl bash

# Copiar configuracion
COPY nginx.conf /etc/nginx/nginx.conf
COPY conf.d /etc/nginx/conf.d
COPY entrypoint.sh /entrypoint.sh

# Simplificar entrypoint: solo generar cert si falta (apk ya esta instalado)
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/bin/bash", "/entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]
