# Build stage
FROM composer:2 as build

WORKDIR /app

# Copy only the files necessary for composer install
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts

# Copy the rest of the application code
COPY . .

# Run any necessary build scripts
RUN composer dump-autoload --optimize

# Production stage
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-install zip pdo_mysql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install xdebug-3.3.2

# Copy application files from build stage
COPY --from=build /app /var/www/html

# Set working directory
WORKDIR /var/www/html

# Set file permissions
RUN chmod -R 755 /var/www/html \
    && chown -R www-data:www-data /var/www/html

# Create Nginx configuration
RUN echo 'user www-data;\n\
worker_processes auto;\n\
pid /run/nginx.pid;\n\
events {\n\
    worker_connections 1024;\n\
}\n\
http {\n\
    server {\n\
        listen $PORT default_server;\n\
        server_name _;\n\
        root /var/www/html/public;\n\
        index index.php index.html index.htm;\n\
        location / {\n\
            try_files $uri $uri/ /index.php?$query_string;\n\
        }\n\
        location ~ \.php$ {\n\
            fastcgi_pass 127.0.0.1:9000;\n\
            fastcgi_index index.php;\n\
            include fastcgi_params;\n\
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
            fastcgi_param PATH_INFO $fastcgi_path_info;\n\
        }\n\
    }\n\
}' > /etc/nginx/nginx.conf

# Create a startup script
RUN echo '#!/bin/bash\n\
sed -i -e "s/\$PORT/$PORT/g" /etc/nginx/nginx.conf\n\
php-fpm -D\n\
nginx -g "daemon off;"' > /start.sh \
&& chmod +x /start.sh

# Expose the port for Cloud Run
ENV PORT 8080
EXPOSE 8080

# Start Nginx and PHP-FPM
CMD ["/start.sh"]
