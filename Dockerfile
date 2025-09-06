# ========= Etapa 1: Build de assets con Node =========
FROM node:18-alpine AS nodebuild
WORKDIR /app

# Dependencias de Node (usa ci si hay lock, si no instala normal)
COPY package.json package-lock.json ./
RUN if [ -f package-lock.json ]; then npm ci; else npm i; fi

# Copiamos el código necesario para construir assets
COPY . .

# Si existe script de build, ejecútalo; si no, continúa
RUN if npm run | grep -q " build"; then npm run build; else echo "No frontend build script; skipping"; fi


# ========= Etapa 2: PHP + Laravel =========
FROM php:8.2-cli

# Paquetes del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install intl bcmath gd zip pdo pdo_mysql opcache \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copiamos el repo (código PHP)
COPY . .

# Copiamos SOLO los assets generados (si existen) desde la etapa de Node
# Al copiar el directorio build se incluye manifest.json si Vite lo generó.
COPY --from=nodebuild /app/public/build /app/public/build

# Crear rutas de cache antes de que Composer dispare artisan scripts
RUN mkdir -p \
      storage/framework/cache \
      storage/framework/sessions \
      storage/framework/views \
      bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# .env: si hay .env.example lo copiamos, si no generamos uno mínimo
RUN if [ ! -f .env ]; then \
      if [ -f .env.example ]; then cp .env.example .env; \
      else echo -e "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost" > .env; fi; \
    fi

# Composer (instala dependencias sin dev y optimiza autoloader)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer install --no-dev --optimize-autoloader

# Puerto para php artisan serve
EXPOSE 8000

# Arranque de la app
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

