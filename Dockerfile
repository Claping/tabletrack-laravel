# Etapa de construcción
FROM composer:2 as build

WORKDIR /app

COPY . .

# Crear carpeta y dar permisos a bootstrap/cache antes del composer install
RUN mkdir -p bootstrap/cache && chmod -R 775 bootstrap/cache

RUN composer install --no-dev --optimize-autoloader

# Etapa final: PHP + extensiones necesarias
FROM php:8.2-cli

WORKDIR /app

COPY --from=build /app /app

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql zip

# Exponer el puerto
EXPOSE 8000

# Comando para levantar el servidor Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
