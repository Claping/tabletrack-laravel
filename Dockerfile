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

# BUST de caché (cambia el valor si hace falta para forzar rebuild)
ARG CACHEBUST=2025-09-06-02-10
RUN echo "CACHEBUST=$CACHEBUST"

# 👇 Copiamos explícitamente .env.example ANTES del COPY global (evita caché)
COPY .env.example .env.example

# Copiamos el resto del proyecto (si esta capa se cachea, ya tenemos el .env.example garantizado arriba)
COPY . .

# .env: si existe .env.example lo usamos; si no, creamos uno mínimo
RUN if [ -f .env.example ]; then cp .env.example .env; \
    else echo -e "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost" > .env; fi

# Bootstrap cache
RUN mkdir -p bootstrap/cache && chmod -R 775 bootstrap/cache

# -----------------------------
# Composer (prod) + APP_KEY
# -----------------------------
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer install --no-dev --optimize-autoloader \
 && php artisan key:generate

# -----------------------------
# Arranque
# -----------------------------
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
