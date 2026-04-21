#!/bin/sh
set -e

echo "Starting Laravel setup..."

mkdir -p storage/framework/{cache,sessions,views} \
         storage/logs \
         bootstrap/cache

# Laravel optimizations (safe on deploy)
php artisan config:clear || true
php artisan cache:clear || true
php artisan config:cache || true
php artisan route:cache || true

# Storage link (ignore failure)
php artisan storage:link --quiet || true

# Run migrations safely
php artisan migrate --force || true

echo "Starting Laravel server..."

exec php artisan serve \
    --host=0.0.0.0 \
    --port=${PORT:-10000}