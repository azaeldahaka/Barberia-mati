#!/bin/sh

# Ejecutar migraciones en produccion
# IMPORTANTE: Usamos --force pero NUNCA migrate:fresh
# fresh boraria todos los datos. Solo 'migrate' agrega los cambios nuevos.
php artisan migrate --force

# Cachear configuracion, rutas y vistas (mejora el rendimiento)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Redirigir el log de Laravel a stderr para que aparezca en el panel de Render
touch /var/www/html/storage/logs/laravel.log
chown www-data:www-data /var/www/html/storage/logs/laravel.log
ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log

# Iniciar Apache en primer plano
exec apache2-foreground
