# Dockerfile para aplicación Laravel_Admin
FROM php:8.4-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP básicas
RUN docker-php-ext-install mbstring bcmath

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código de la aplicación
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Copiar archivo de configuración de Laravel
RUN cp .env.example .env

# Generar clave de aplicación
RUN php artisan key:generate

# Crear base de datos SQLite y configurar
RUN touch database/database.sqlite

# Dar permisos al directorio storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer el puerto
EXPOSE 8000

# Comando para ejecutar la aplicación
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
