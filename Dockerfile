FROM php:8.2-cli

# -----------------------------
# Paquetes del sistema + extensiones PHP
# -----------------------------
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    intl \
    bcmath \
    gd \
    zip \
    pdo \
    pdo_mysql \
    opcache

# -----------------------------
# Código de la app
# -----------------------------
WORKDIR /app

# BUST de caché (cámbialo si necesitas forzar rebuild)
ARG CACHEBUST=2025-09-06-02-20
RUN echo "CACHEBUST=$CACHEBUST"

# Copiamos .env.example explícitamente para evitar caché del COPY global
COPY .env.example .env.example

# Copiamos el resto del proyecto
COPY . .

# .env: si existe .env.example lo usamos; si no, creamos uno mínimo
RUN if [ -f .env.example ]; then cp .env.example .env; \
    else echo -e "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost" > .env; fi

# Crear ESTRUCTURA de cache y dar permisos (esto resuelve "valid cache path")
RUN mkdir -p \
      bootstrap/cache \
      storage/framework/cache/data \
      storage/framework/sessions \
      storage/framework/views \
      storage/logs \
 && chmod -R 775 bootstrap/cache storage \
 && chown -R www-data:www-data bootstrap/cache storage || true

# -----------------------------
# Composer (prod) + APP_KEY
# -----------------------------
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer install --no-dev --optimize-autoloader \
 && php artisan key:generate

# -----------------------------
# Arranque
# -----------------------------
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
