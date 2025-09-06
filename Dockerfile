# -----------------------------
# 1) Construir assets con Node
# -----------------------------
FROM node:18-alpine AS nodebuild
WORKDIR /app

# Cacheo de dependencias
COPY package.json package-lock.json ./
RUN npm ci

# Archivos de configuración + fuentes
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY public ./public

# Compilar Vite (genera public/build y su manifest dentro de esa carpeta)
RUN npm run build

# -----------------------------
# 2) Imagen de PHP (Laravel)
# -----------------------------
FROM php:8.2-cli

# Paquetes del sistema + extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install intl bcmath gd zip pdo pdo_mysql opcache \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copiamos el proyecto
COPY . .

# Copiamos solo los assets ya compilados por Vite
COPY --from=nodebuild /app/public/build /app/public/build

# Si no existe .env, crear uno base
RUN if [ ! -f .env ]; then \
      if [ -f .env.example ]; then cp .env.example .env; \
      else echo -e "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost" > .env; fi \
    ; fi

# Permisos y estructura de cache/logs
RUN mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs \
 && chmod -R 775 storage bootstrap/cache

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer install --no-dev --optimize-autoloader

# APP_KEY (si hiciera falta) y symlink de storage
RUN php artisan key:generate --force || true
RUN php artisan storage:link || true

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

