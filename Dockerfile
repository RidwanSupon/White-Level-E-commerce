# Multi-Stage Production Dockerfile for Laravel 13 + PHP 8.4 + Node Vite
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies & PHP extensions required by Laravel & PostgreSQL
# Cache Invalidation Token: v2.0-render-clean-build
RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    postgresql-dev \
    oniguruma-dev \
    nginx \
    supervisor \
    bash \
    curl \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        bcmath \
        mbstring \
        opcache \
        gd \
        zip \
        intl

# Install Composer binary from official image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copy application source code
COPY . /var/www/html

# Install PHP dependencies without dev dependencies
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Install Node dependencies and compile production frontend assets via Vite
RUN npm install --ignore-scripts && npm run build

# Nginx & Supervisor Configurations
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup File Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Make Entrypoint Executable
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
