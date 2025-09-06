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
# Copiamos todo (puede quedar cacheado)
COPY . .
# Forzamos a copiar .env.example aunque haya caché en la capa anterior
COPY .env.example .env.example

# .env: si existe .env.example lo usamos; si no, creamos uno mínimo para que no falle el build
RUN if [ -f .env.example ]; then cp .env.example .env; \
    else echo -e "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost" > .env; fi

# Bootstrap cache
RUN mkdir -p bootstrap/cache && chmod -R 775 bootstrap/cache

# BUST de caché para obligar a reconstruir capas cuando lo necesitemos (cambia el valor si hace falta)
ARG CACHEBUST=2025-09-06-02-05
RUN echo "CACHEBUST=$CACHEBUST"

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
