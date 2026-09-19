#!/bin/sh

# Ejecutar migraciones en produccion
# IMPORTANTE: Usamos --force pero NUNCA migrate:fresh
# fresh boraria todos los datos. Solo 'migrate' agrega los cambios nuevos.
php artisan migrate --force

# Cachear configuracion, rutas y vistas (mejora el rendimiento)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar Apache en primer plano
exec apache2-foreground
