FROM php:8.1-fpm

# Instalar extensões do PHP e dependências
RUN apt-get update && apt-get install -y \
    libzip-dev unzip curl \
    && docker-php-ext-install pdo pdo_mysql zip pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
