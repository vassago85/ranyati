# Stage 1: Build frontend assets with Node 22
FROM node:22-alpine AS frontend
WORKDIR /build
COPY apps/group/package.json apps/group/package-lock.json ./
RUN npm ci
COPY apps/group/ ./
RUN npm run build

# Stage 2: PHP application
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    netcat-openbsd \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY apps/group/ .

RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy pre-built frontend assets from stage 1
COPY --from=frontend /build/public/build/ public/build/

# Verify the build output exists
RUN test -f public/build/manifest.json || (echo "ERROR: Vite manifest missing" && exit 1)

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN mkdir -p /var/log/supervisor /run/nginx /var/log/php

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
