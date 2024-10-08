# Use the official Apache image as a base
FROM php:apache

WORKDIR /var/www/coordinatorr

ENV DEBIAN_FRONTEND=noninteractive

# Add PHP necessary extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd pgsql opcache bcmath zip

# Enable the Apache rewrite module
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Create and configure the virtual host file
RUN mkdir -p /etc/apache2/sites-available /var/www/coordinatorr/public && \
    echo -e '<VirtualHost *:80> \nDocumentRoot "/var/www/coordinatorr/public"\n<Directory /var/www/coordinatorr/public>\nOptions Indexes MultiViews FollowSymLinks\nAllowOverride All\nRequire all granted\n</Directory>\nErrorLog ${APACHE_LOG_DIR}/error.log \nCustomLog ${APACHE_LOG_DIR}/access.log combined\n</VirtualHost>' > /etc/apache2/sites-available/coordinatorr.conf
# Enable the new virtual host
RUN a2ensite coordinatorr.conf && \
    service apache2 restart

COPY . .
USER www-data
RUN composer install --no-dev
COPY .env.example .env
RUN php artisan key:generate

# force sqlite by default
RUN sed -i "s/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/" .env
RUN sed -i "s/DB_DATABASE=laravel/DB_DATABASE=database.sqlite/" .env
RUN touch /var/www/coordinatorr/database/database.sqlite
VOLUME /var/www/coordinatorr/database

# Prepare database
RUN php artisan migrate
RUN php artisan storage:link
USER root
RUN service apache2 restart
USER www-data

# Expose port 80 for Apache server
EXPOSE 80
