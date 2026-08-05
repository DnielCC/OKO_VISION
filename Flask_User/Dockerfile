# Dockerfile para aplicación Flask_User
FROM python:3.11-slim

# Establecer el directorio de trabajo
WORKDIR /app

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    gcc \
    sqlite3 \
    && rm -rf /var/lib/apt/lists/*

# Copiar requirements.txt primero para aprovechar el cache de Docker
COPY requirements.txt .

# Instalar dependencias de Python
RUN pip install --no-cache-dir -r requirements.txt

# Copiar el código de la aplicación
COPY . .

# Exponer el puerto
EXPOSE 5000

# Variables de entorno
ENV FLASK_APP=main.py
ENV FLASK_ENV=production
ENV FLASK_DEBUG=0
ENV PYTHONUNBUFFERED=1

# Comando para ejecutar la aplicación con Gunicorn en producción
CMD ["sh", "-c", "gunicorn --workers ${GUNICORN_WORKERS:-4} --threads ${GUNICORN_THREADS:-8} --worker-class gthread --bind 0.0.0.0:5000 --timeout ${GUNICORN_TIMEOUT:-120} --keep-alive ${GUNICORN_KEEPALIVE:-15} 'main:create_app()'"]
