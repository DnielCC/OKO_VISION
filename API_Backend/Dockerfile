FROM python:3.11-slim

WORKDIR /app

# Add current directory to Python path
ENV PYTHONPATH=/app

# Instalar dependencias del sistema para OpenCV y bibliotecas gráficas
RUN apt-get update && apt-get install -y \
    libpq-dev \
    gcc \
    libgl1 \
    libglib2.0-0 \
    libsm6 \
    libxext6 \
    libxrender-dev \
    libgomp1 \
    && rm -rf /var/lib/apt/lists/*

# Instalar PyTorch y torchvision de CPU primero
RUN pip install --no-cache-dir torch>=2.0.0 torchvision>=0.15.0 --index-url https://download.pytorch.org/whl/cpu

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

EXPOSE 8000

CMD ["sh", "-c", "uvicorn app.main:app --host 0.0.0.0 --port 8000 --workers ${UVICORN_WORKERS:-4} --backlog ${UVICORN_BACKLOG:-2048}"]
