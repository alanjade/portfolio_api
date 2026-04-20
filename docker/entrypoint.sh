#!/bin/sh
set -e

echo "──────────────────────────────────────────"
echo "  Portfolio API — starting up"
echo "──────────────────────────────────────────"

# Clear cached config so Render's injected env vars take effect at runtime
php artisan config:clear

# Run migrations first (creates the cache table)
echo "→ Running migrations..."
php artisan migrate --force

# Seed the database (creates admin user if not exists)
echo "→ Seeding database..."
php artisan db:seed --force

# Now safe to clear cache (table exists)
echo "→ Clearing cache..."
php artisan cache:clear

# Re-cache config now that real env vars are loaded
echo "→ Caching config..."
php artisan config:cache
php artisan route:cache

echo "→ Starting services (nginx + php-fpm)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf