FROM php:8.2-cli

# SO + extensiones
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install intl bcmath gd zip pdo pdo_mysql opcache \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# (Opcional) bust de caché para forzar copia fresca
RUN echo "CACHEBUST=2025-09-06-02-40"

# Copiamos primero composer.* para aprovechar cache de dependencias
COPY composer.json composer.lock ./

# **Crear rutas de cache ANTES del composer install**
RUN mkdir -p bootstrap/cache \
    && mkdir -p storage/framework/{cache,data,sessions,testing,views} \
    && chmod -R 775 bootstrap/cache storage

# Instalar composer y dependencias **sin scripts** (evita package:discover en build)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer install --no-dev --optimize-autoloader --no-scripts

# Copia del resto del código
COPY . .

# Si tienes build de assets con Node/Vite, déjalo como lo tenías (etapa node) o elimina esa parte si no la usas.

# .env de respaldo si hiciera falta en runtime (el instalador lo ajusta)
RUN if [ ! -f .env ]; then \
      if [ -f .env.example ]; then cp .env.example .env; \
      else printf "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost\n" > .env; \
      fi; \
    fi

# Asegurar permisos finales por si la copia los cambió
RUN chmod -R 775 bootstrap/cache storage

# Servir
CMD ["php","artisan","serve","--host=0.0.0.0","--port=8000"]
