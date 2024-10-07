# Use the official Apache image as a base
FROM php:8.4-rc-apache-bullseye

WORKDIR /var/www/coordinatorr

# Set non-interactive mode for apt
ENV DEBIAN_FRONTEND=noninteractive

# Update package list and install required packages
RUN apt-get update && \
    apt-get install -y \
    apt-transport-https \
    lsb-release \
    ca-certificates \
    gnupg2 \
    wget && \
    apt-get clean

# Add the ondrej/php repository and its GPG key
RUN wget -qO /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
    echo "deb https://packages.sury.org/php/ $(lsb_release -cs) main" \
    | tee /etc/apt/sources.list.d/php.list


# Add PHP repository and install PHP along with necessary extensions
RUN apt-get update -y && \
    apt-get install -y \
    libapache2-mod-php \
    php8.4-common \
    php8.4-xml \
    php8.4-mysql \
    php8.4-pgsql \
    php8.4-gd \
    php8.4-opcache \
    php8.4-mbstring \
    php8.4-tokenizer \
    php8.4-json \
    php8.4-bcmath \
    php8.4-zip \
    php8.4-sqlite3

    # Enable the Apache rewrite module
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Create and configure the virtual host file
RUN mkdir -p /etc/apache2/sites-available /var/www/coordinatorr/public && \
    echo '<VirtualHost *:80> \
    DocumentRoot "/var/www/coordinatorr/public" \
    <Directory /var/www/coordinatorr/public> \
        Options Indexes MultiViews FollowSymLinks \
        AllowOverride All \
        Order allow,deny \
        allow from all \
        Require all granted \
    </Directory> \
    ErrorLog ${APACHE_LOG_DIR}/error.log \
    CustomLog ${APACHE_LOG_DIR}/access.log combined \
</VirtualHost>' > /etc/apache2/sites-available/coordinatorr.conf

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

# Expose port 80 for Apache server
EXPOSE 80

# Set the command to run Apache in the foreground
CMD ["httpd-foreground"]
