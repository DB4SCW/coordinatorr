FROM composer:2 AS vendor

WORKDIR /app

COPY composer.* ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

FROM node:20-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

FROM php:8.5-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

WORKDIR /var/www/html

COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    bcmath \
    gd \
    mbstring \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    zip \
    opcache

RUN useradd --system --no-create-home --shell /usr/sbin/nologin --gid www-data --uid 999 coordinatorr

COPY docker/apache.conf /etc/apache2/conf-available/coordinatorr.conf

RUN a2enmod rewrite \
    && printf '%s\n' 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && a2enconf coordinatorr \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

COPY --chown=coordinatorr:www-data . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN chown -R coordinatorr:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

COPY docker/entrypoint.sh /usr/local/bin/coordinatorr-entrypoint
RUN chmod +x /usr/local/bin/coordinatorr-entrypoint

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --start-period=20s --retries=3 \
    CMD curl -fsS http://127.0.0.1/ || exit 1

ENTRYPOINT ["coordinatorr-entrypoint"]
CMD ["apache2-foreground"]
