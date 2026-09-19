# ============================================================
# Fase 1: Build de Node (compilar assets de React + Vite)
# ============================================================
FROM node:20 AS build
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ============================================================
# Fase 2: PHP + Apache (servidor de produccion)
# ============================================================
FROM php:8.3-apache

# Habilitar mod_rewrite (necesario para que las rutas de Laravel funcionen)
RUN a2enmod rewrite

# Instalar dependencias del sistema para PostgreSQL y Laravel
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    dos2unix \
    && docker-php-ext-install pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer (herramienta de dependencias de PHP)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apuntar Apache a la carpeta public/ de Laravel (no a la raiz del proyecto)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar el codigo del proyecto al contenedor
COPY . .

# Copiar los assets ya compilados de la fase Node
COPY --from=build /app/public/build ./public/build

# Instalar dependencias PHP (sin paquetes de desarrollo, optimizado para produccion)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar y preparar el script de inicio (dos2unix garantiza saltos de linea Unix en Linux)
COPY start.sh /usr/local/bin/start.sh
RUN dos2unix /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Permisos de almacenamiento de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Puerto expuesto (Render usa el 80 por defecto con Docker)
EXPOSE 80

# Comando de inicio del contenedor
CMD ["/usr/local/bin/start.sh"]
