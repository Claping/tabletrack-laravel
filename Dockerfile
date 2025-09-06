# Etapa de construcción
FROM composer:2 as build

WORKDIR /app

COPY . .

RUN mkdir -p bootstrap/cache \
    && composer install --no-dev --optimize-autoloader

# Etapa final
FROM php:8.2-cli

WORKDIR /app

COPY --from=build /app /app

RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql zip

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
