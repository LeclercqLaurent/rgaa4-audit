FROM php:8.4-apache

# Extensions PHP requises (Doctrine/MariaDB, intl, zip, opcache)
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libicu-dev libzip-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" intl pdo_mysql zip opcache \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

# Composer (depuis l'image officielle)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY build/php/conf/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/app
