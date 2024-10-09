# Use the official Apache image as a base
FROM php:apache

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive

# Add PHP necessary extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd pgsql opcache bcmath zip

# Enable the Apache rewrite module
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer
COPY . .
RUN composer install --no-dev
COPY .env.example .env
RUN php artisan key:generate

# force sqlite by default
RUN sed -i "s/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/" .env
RUN sed -i "s/DB_DATABASE=laravel/DB_DATABASE=database.sqlite/" .env
RUN touch /var/www/html/database/database.sqlite
VOLUME /var/www/html/database

# Prepare database
RUN php artisan migrate
RUN php artisan storage:link

# change permissions and set document root
RUN sed -i "s/html/html\/public/g" /etc/apache2/sites-enabled/000-default.conf
RUN chown -R www-data:www-data /var/www/html/* && chmod -R 755 /var/www/html/*
RUN service apache2 restart
USER www-data

# Expose port 80 for Apache server
EXPOSE 80
