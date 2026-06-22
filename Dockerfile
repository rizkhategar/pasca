# =============================================================================
# Dockerfile - Local Development & Proxy Mode
# =============================================================================
# PHP 8.4 with Nginx (HTTP only, no SSL)
# Use this for:
#   - Local development (docker-compose.yml)
#   - Production behind Nginx proxy (docker-compose.prod.yml)
# =============================================================================

FROM php:8.4-fpm

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libsqlite3-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
    pdo_mysql \
    pdo_sqlite \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy nginx, php-fpm, and supervisor configuration
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy security scripts
COPY docker/scripts/cleanup-php.sh /usr/local/bin/cleanup-php.sh
COPY docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/cleanup-php.sh /usr/local/bin/entrypoint.sh

# Copy application files
COPY . /var/www/html

# Install Composer dependencies (Laravel framework)
# We use --ignore-platform-reqs because sometimes local lock file hashes differ or extensions mismatch slightly
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Install Node.js dependencies
RUN npm ci

# Run build:all to build all Vite configurations (frontend, auth, backend)
RUN npm run build

# Generate application key
RUN php artisan key:generate

# Create storage link
RUN php artisan storage:link

# Set permissions for Laravel storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Forward nginx logs to docker log collector
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

# Expose port 80
EXPOSE 80

# Use custom entrypoint that starts security script + Nginx
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
