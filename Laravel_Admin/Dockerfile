# Dockerfile para aplicación Laravel_Admin optimizado
FROM php:8.4-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP básicas y opcache
RUN docker-php-ext-install mbstring bcmath opcache

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

# Exponer el puerto
EXPOSE 8000

# Comando para ejecutar la aplicación con configuración estándar de desarrollo
CMD php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=8000
