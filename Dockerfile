# ── Stage 1: Composer dependencies ───────────────────────────────────────────
FROM php:8.2-cli-alpine AS vendor

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

# Install production dependencies only (no dev)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ── Stage 2: Production image ─────────────────────────────────────────────────
FROM php:8.2-fpm-alpine

# ── System dependencies ───────────────────────────────────────────────────────
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    libzip-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        mbstring \
        bcmath \
        zip \
        intl \
        opcache \
    && rm -rf /var/cache/apk/*

# ── PHP config ────────────────────────────────────────────────────────────────
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# ── Nginx config ──────────────────────────────────────────────────────────────
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# ── Supervisor config ─────────────────────────────────────────────────────────
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ── App ───────────────────────────────────────────────────────────────────────
WORKDIR /var/www/html

# Copy vendor from Stage 1
COPY --from=vendor /app/vendor ./vendor

# Copy application source
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Generate app key, cache config (key will be overridden by Render env var)
RUN php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Storage symlink for public image URLs (storage/app/public → public/storage)
RUN php artisan storage:link

EXPOSE 80

# Entrypoint: run migrations then start nginx + php-fpm via supervisor
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]