FROM php:8.4-fpm-alpine

# Системные зависимости
RUN apk add --no-cache \
    git \
    unzip \
    curl \
    libpq-dev \
    docker-cli \
    && docker-php-ext-install pdo pdo_pgsql

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Зависимости composer отдельным слоем для кэширования
COPY composer.json composer.lock* ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize