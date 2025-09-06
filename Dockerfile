# -----------------------------
# 1) Construir assets con Node
# -----------------------------
FROM node:18-alpine AS nodebuild
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# -----------------------------
# 2) Imagen de PHP (Laravel)
# -----------------------------
FROM php:8.2-cli

# Sistema + extensiones PHP
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install intl bcmath gd zip pdo pdo_mysql opcache \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Código y assets compilados
COPY . .
COPY --from=nodebuild /app/public/build /app/public/build

# .env base si no existe
RUN if [ ! -f .env ]; then \
      if [ -f .env.example ]; then cp .env.example .env; \
      else echo -e "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost" > .env; fi \
    ; fi

# **Crear rutas de cache y logs ANTES de Composer**
RUN mkdir -p bootstrap/cache \
    storage/framework/{cache,sessions,views} \
    storage/logs \
 && chmod -R 775 storage bootstrap/cache

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer install --no-dev --optimize-autoloader

# Ajustes post-install (no fallar si ya están hechos)
RUN php artisan key:generate --force || true
RUN php artisan storage:link || true

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
