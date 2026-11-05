#!/bin/bash
set -e

cd /var/www/html

# Ensure storage directories exist and have proper permissions for web server
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Install dependencies if vendor does not exist
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
fi

# Generate APP_KEY if not set
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Create storage symlink
php artisan storage:link || true

# Run database migrations
echo "Running migrations..."
php artisan migrate --force || echo "Migration warning: check DB connectivity"

# Cache optimizations for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting web server..."
exec "$@"
