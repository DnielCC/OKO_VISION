# Usar la imagen oficial de PHP 8.3 con Apache (perfecta para Laravel)
FROM php:8.3-apache

# Instalar dependencias del sistema y extensiones PHP necesarias para Laravel y PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    bash \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite de Apache para Laravel
RUN a2enmod rewrite

# Copiar configuración personalizada de Apache
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Composer
RUN composer install --optimize-autoloader --no-dev --ignore-platform-reqs

# Configurar permisos correctos para Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Dar permisos de ejecución al script de arranque
RUN chmod +x /var/www/html/entrypoint.sh

# Exponer el puerto 80 (Apache usa este puerto por defecto)
EXPOSE 80

# Comando para ejecutar el script de arranque
ENTRYPOINT ["/var/www/html/entrypoint.sh"]
