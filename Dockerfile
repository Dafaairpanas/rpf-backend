# ============================================
# Multi-stage Dockerfile for Laravel 11
# Optimized for Render.com deployment
# ============================================

# ---- Stage 1: Composer Dependencies ----
FROM composer:2 AS composer-builder

WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install dependencies without dev packages
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# Copy the rest of the application
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# ---- Stage 2: Node.js Build (for Vite assets) ----
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci --silent

# Copy source files needed for build
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

# Build assets
RUN npm run build

# ---- Stage 3: Production Image ----
FROM php:8.2-fpm-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite \
    sqlite-dev \
    oniguruma-dev \
    icu-dev \
    postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_sqlite \
    pdo_pgsql \
    gd \
    zip \
    bcmath \
    opcache \
    intl \
    mbstring \
    && rm -rf /var/cache/apk/*

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy PHP configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Copy Supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy application from composer stage
COPY --from=composer-builder /app/vendor ./vendor
COPY . .

# Copy built assets from node stage
COPY --from=node-builder /app/public/build ./public/build

# Create necessary directories and set permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && mkdir -p database \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Create SQLite database file if using SQLite
RUN touch database/database.sqlite \
    && chown www-data:www-data database/database.sqlite \
    && chmod 664 database/database.sqlite

# Expose port (Railway sets PORT dynamically)
EXPOSE ${PORT:-8080}

# Copy and set entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:${PORT:-8080}/api/health || exit 1

ENTRYPOINT ["/entrypoint.sh"]
