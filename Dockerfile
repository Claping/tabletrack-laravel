# =========================
# Etapa 1: Build de assets
# =========================
FROM node:18-alpine AS nodebuild
WORKDIR /app

# Copiamos solo lo necesario para instalar y construir
COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./
RUN if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
    else npm i; fi

COPY vite.config.js tailwind.config.js postcss.config.js . 2>/dev/null || true
COPY resources ./resources
COPY public ./public

# Construir assets (Vite crea /public/build)
RUN npm run build || (echo "Aviso: fallo build de front, continúa backend" && true)

# =========================
# Etapa 2: PHP + Laravel
# =========================
FROM php:8.2-cli

# Dependencias de sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install intl bcmath gd zip pdo pdo_mysql opcache \
 && rm -rf /var/lib/apt/lists/*

# Directorio de trabajo
WORKDIR /app

# Opcional: bust de cache de BuildKit si lo necesitas
ARG CACHEBUST=1
RUN echo "CACHEBUST=$CACHEBUST"

# Copiamos el código de la app
COPY . .

# Asegurar que exista .env (usar ejemplo si está presente)
RUN if [ -f .env.example ]; then cp .env.example .env; \
    else printf "APP_NAME=Laravel\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=http://localhost\n" > .env; fi

# Copiamos los assets generados por Vite (si existen)
# (El directorio existe si npm run build se ejecutó bien)
COPY --from=nodebuild /app/public/build /app/public/build

# Crear rutas de cache/storage que Laravel necesita y dar permisos
RUN mkdir -p /app/bootstrap/cache \
             /app/storage/framework/sessions \
             /app/storage/framework/views \
             /app/storage/framework/cache \
 && chmod -R 777 /app/bootstrap/cache /app/storage

# Composer (instalar dependencias sin dev)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer install --no-dev --optimize-autoloader

# Generar APP_KEY si falta (ignora error si ya existe)
RUN php -r "file_exists('.env') || exit(0); \
    \$env=file_get_contents('.env'); \
    if(strpos(\$env,'APP_KEY=')!==false && preg_match('/^APP_KEY=\\s*$/m',\$env)){ \
      passthru('php artisan key:generate'); \
    }"

# Exponer puerto y arrancar servidor php artisan
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
