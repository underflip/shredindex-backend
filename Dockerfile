# Build stage
FROM composer:2 AS build

# Use build argument to pass the secret
ARG OCTOBER_CMS_AUTH

# Create the .composer directory and then create auth.json from the secret
RUN mkdir -p /root/.composer && \
    echo '$OCTOBER_CMS_AUTH' > /root/.composer/auth.json

# Debug: Print out the contents of auth.json (remove this before production)
RUN cat /root/.composer/auth.json

# Debug: Check if the file exists and has the correct permissions
RUN ls -la /root/.composer/auth.json

# Copy only composer files to install dependencies
COPY composer.json composer.lock ./

# Install dependencies with verbose output
RUN composer install --prefer-dist --no-dev --no-scripts --optimize-autoloader -vvv

# Copy only necessary application source files to the build context
COPY . .

# Run necessary build scripts and optimizations
RUN composer dump-autoload --optimize

# Production stage
FROM php:8.2-fpm

# Install necessary PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    nginx \
    && docker-php-ext-install zip pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install xdebug-3.3.2 \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Configure PHP extensions (e.g., Opcache)
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=60'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Copy the application code from the build stage to the production stage
COPY --from=build /app /var/www/html

# Set the working directory
WORKDIR /var/www/html

# Set appropriate permissions for Laravel directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy the Nginx configuration and startup script (optional, if using Nginx)
COPY .infrastructure/etc/nginx/conf.d/default.conf /etc/nginx/sites-available/default

# Expose the port for Cloud Run
EXPOSE 8080

# Create a startup script to launch PHP-FPM and Nginx together
RUN echo '#!/bin/bash\n\
php-fpm -D\n\
nginx -g "daemon off;"' > /start.sh \
    && chmod +x /start.sh

# Start the services using the startup script
CMD ["/start.sh"]
