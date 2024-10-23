# Build stage
FROM composer:2 AS build

# Use build argument to pass the secret
ARG OCTOBER_CMS_AUTH

ARG OCTOBER_CMS_USERNAME

ARG OCTOBER_CMS_PASSWORD

# Create the .composer directory and then create auth.json from the secret
RUN mkdir -p /root/.composer && \
    echo $OCTOBER_CMS_AUTH > /root/.composer/auth.json

# Debug: Print out the contents of auth.json (remove this before production)
RUN echo "Contents of auth.json:" && cat /root/.composer/auth.json

# Debug: Check if the file exists and has the correct permissions
RUN ls -la /root/.composer/auth.json

# Copy only composer files to install dependencies
COPY composer.json composer.lock ./

RUN composer config -g http-basic.gateway.octobercms.com username $OCTOBER_CMS_AUTH
RUN composer config -g http-basic.gateway.octobercms.com password $OCTOBER_CMS_PASSWORD

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

# Debug: List contents of /var/www/html
RUN echo "Contents of /var/www/html:" && ls -la /var/www/html

# Debug: Check for bootstrap directory
RUN echo "Checking for bootstrap directory:" && ls -la /var/www/html/bootstrap || echo "bootstrap directory not found"

# Create necessary directories and set appropriate permissions
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && ([ -d /var/www/html/storage ] && chown -R www-data:www-data /var/www/html/storage || true) \
    && ([ -d /var/www/html/bootstrap/cache ] && chown -R www-data:www-data /var/www/html/bootstrap/cache || true) \
    && ([ -d /var/www/html/storage ] && chmod -R 775 /var/www/html/storage || true) \
    && ([ -d /var/www/html/bootstrap/cache ] && chmod -R 775 /var/www/html/bootstrap/cache || true)

# Copy the Cloud Run specific Nginx configuration
COPY .infrastructure/etc/nginx/conf.d/deploy.conf.dist /etc/nginx/sites-available/default

# Expose the port for Cloud Run
EXPOSE 8080

# Create a startup script to launch PHP-FPM and Nginx together
RUN echo '#!/bin/bash\n\
php-fpm -D\n\
nginx -g "daemon off;"' > /start.sh \
    && chmod +x /start.sh

# Start the services using the startup script
CMD ["/start.sh"]