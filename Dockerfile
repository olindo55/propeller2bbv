# Using PHP 8.3 with Apache base image
FROM php:8.3-apache

# Installing system dependencies
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Installing required PHP extensions
RUN docker-php-ext-install zip

# Installing Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache configuration
RUN a2enmod rewrite

# Setting working directory
WORKDIR /var/www/html

# Copying application files
COPY . .

# Installing dependencies with Composer
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Setting up permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposing port 80
EXPOSE 80