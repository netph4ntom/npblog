FROM php:8.3-fpm-alpine

RUN apk add --no-cache git unzip curl libzip-dev libpng-dev libjpeg-turbo-dev libwebp-dev \
      freetype-dev icu-dev oniguruma-dev mysql-client \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd zip intl bcmath opcache exif pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

WORKDIR /var/www/html
USER www-data
