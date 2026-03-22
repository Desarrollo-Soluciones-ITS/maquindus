# ============================================================
# Stage 1: PHP dependencies
# ============================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist \
    --ignore-platform-reqs \
    --no-scripts

# ============================================================
# Stage 2: Build assets (Node + Vite)
# Necesita vendor/ porque app.css importa el CSS de Filament
# ============================================================
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit

COPY vite.config.js ./
COPY resources/ ./resources/
# Filament publica CSS desde vendor — debe estar antes del build
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# ============================================================
# Stage 3: Final image
# ============================================================
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    curl \
    unzip \
    git \
    bash

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
        pdo_mysql \
        pdo_sqlite \
        gd \
        zip \
        intl \
        mbstring \
        bcmath \
        opcache \
        pcntl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /var/www/html

# Copy app files
COPY --chown=www-data:www-data . .

# Copy compiled assets from Stage 2
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# Copy vendor from Stage 1
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor

# Copy config files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini $PHP_INI_DIR/conf.d/app.ini

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]