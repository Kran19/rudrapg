#!/bin/bash
set -e

cd /var/www/html

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
