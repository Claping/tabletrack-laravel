FROM php:8.2-cli

# Instalar dependencias del sistema y extensiones necesarias para Laravel
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

# Directorio de trabajo
WORKDIR /app

# Copiar código de la app
COPY . .

# Copiar archivo .env por defecto
RUN cp .env.example .env

# Crear carpeta y dar permisos necesarios
RUN mkdir -p bootstrap/cache && chmod -R 775 bootstrap/cache

# Instalar Composer y dependencias de producción
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-dev --optimize-autoloader && \
    php artisan key:generate

# Servir la aplicación en puerto 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
