#!/bin/sh
set -e

# Ejecutar migraciones en produccion
# IMPORTANTE: Usamos --force pero NUNCA migrate:fresh
# fresh boraria todos los datos. Solo 'migrate' agrega los cambios nuevos.
php artisan migrate --force

# Ejecutar seeders para asegurar que los datos base (roles, admin) existan
# Todos nuestros seeders usan firstOrCreate, por lo que es seguro ejecutarlo en cada inicio.
php artisan db:seed --force

# Cachear configuracion, rutas y vistas (mejora el rendimiento)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Asignar permisos al storage por las dudas (root crea los caches arriba)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Iniciar Apache en primer plano
exec apache2-foreground
