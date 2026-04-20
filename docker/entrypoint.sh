#!/bin/sh
set -e

echo "──────────────────────────────────────────"
echo "  Portfolio API — starting up"
echo "──────────────────────────────────────────"

# Clear cached config so Render's injected env vars take effect at runtime
php artisan config:clear
php artisan cache:clear

# Run migrations against Supabase (safe with --force in production)
echo "→ Running migrations..."
php artisan migrate --force

# Re-cache config now that real env vars are loaded
echo "→ Caching config..."
php artisan config:cache
php artisan route:cache

echo "→ Starting services (nginx + php-fpm)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
