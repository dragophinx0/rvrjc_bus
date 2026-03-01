#!/bin/sh

# Exit on error
set -e

# Generate app key if not set (fallback)
if [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY is not set. Generating one..."
    php artisan key:generate --show
fi

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (ensure DB is ready)
echo "Running migrations..."
php artisan migrate --force

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
