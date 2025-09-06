FROM php:8.2-cli

# Instalar dependencias del sistema y extensiones de PHP necesarias
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

# Configuración adicional
WORKDIR /app
COPY . .

# Permisos para Laravel
RUN mkdir -p bootstrap/cache && chmod -R 775 bootstrap/cache

# Instalar dependencias de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-dev --optimize-autoloader

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
