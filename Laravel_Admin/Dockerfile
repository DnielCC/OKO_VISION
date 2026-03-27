# Dockerfile para aplicación Laravel_Admin optimizado
FROM php:8.4-cli

# Instalar dependencias del sistema y drivers de PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libonig-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP incluyendo pdo_pgsql
RUN docker-php-ext-install mbstring bcmath opcache pdo_pgsql pgsql

# Configuración agresiva de Opcache para máxima velocidad
RUN { \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.revalidate_freq=0'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-optimized.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias con Composer y optimizar
RUN composer install --optimize-autoloader --no-dev --ignore-platform-reqs

# Permisos para storage y cache
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs
RUN chmod -R 777 storage bootstrap/cache

# Exponer el puerto
EXPOSE 8000

# Comando para ejecutar la aplicación
CMD php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan optimize && \
    php artisan serve --host=0.0.0.0 --port=8000 --no-reload
