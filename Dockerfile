FROM php:8.3-fpm

WORKDIR /var/www/html
ENV HOME=/var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl zip unzip supervisor \
    libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --optimize-autoloader --no-dev \
    && rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && php artisan package:discover --ansi \
    && mkdir -p storage/app/public \
    && mkdir -p storage/framework/{cache,sessions,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x entrypoint.sh

EXPOSE 9000

USER www-data

CMD ["./entrypoint.sh"]