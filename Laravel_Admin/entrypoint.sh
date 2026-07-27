#!/bin/bash

# Asegurar que las carpetas de caché existan y tengan permisos correctos
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache/data

# Establecer permisos correctos para Laravel
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ejecutar migraciones y seeders
php artisan migrate --force
php artisan db:seed --force

# Limpiar caché de Laravel
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Iniciar Apache
exec apache2-foreground
