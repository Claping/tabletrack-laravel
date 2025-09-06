# -----------------------------
# 1) Construir assets con Node
# -----------------------------
FROM node:18-alpine AS nodebuild
WORKDIR /app

# Solo lo necesario para cachear mejor
COPY package.json package-lock.json ./
RUN npm ci

# Copiamos el resto de archivos necesarios para el build de Vite
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY public ./public

# Compilamos (esto generará public/build y manifest)
RUN npm run build

# -----------------------------
# 2) Imagen de PHP (Laravel)
# -----------------------------
FROM php:8.2-cli

# Dependencias del sistema + extensiones requeridas por Composer/paquetes
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install intl bcmath gd zip pdo pdo_mysql opcache \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copiamos TODO el proyecto
COPY . .

# Copiamos los assets construidos en la etapa de Node (ignora .dockerignore)
COPY --from=nodebuild /app/public/build /app/public/build
COPY --from=nodebuild /app/public/manifest.json /app/public/manifest.json

# Si no existe .env aún, crear uno base (el instalador luego lo ajusta)
RUN if [ ! -f .env ]; then \
      if [ -f .env.example ]; then cp .env.example .env; \
      else echo -e "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost" > .env; fi \
    ; fi

# Permisos necesarios
RUN mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs \
 && chmod -R 775 storage bootstrap/cache

# Instalar Composer y dependencias PHP
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer install --no-dev --optimize-autoloader

# Generar APP_KEY si está vacío (no rompe si ya existe)
RUN php -r "file_exists('.env') && preg_match('/^APP_KEY=\\s*$/m', file_get_contents('.env')) ? exit(0) : exit(0);" \
 && php artisan key:generate --force || true

# Crear symlink de storage por si el instalador lo requiere
RUN php artisan storage:link || true

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

